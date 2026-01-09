<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Order;
use App\Exceptions\WrongCaseNumberException;
use App\Form\ConfirmOrderDetailsForm;
use App\Form\DeclarationForm;
use App\Form\OrderForm;
use App\Service\DocumentService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private EntityManager $em;

    private OrderService $orderService;

    private DocumentService $documentService;

    public function __construct(
        EntityManager $em,
        OrderService $orderService,
        DocumentService $documentService,
    ) {
        $this->em = $em;
        $this->orderService = $orderService;
        $this->documentService = $documentService;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/order/{orderId}/edit', name: 'order-edit')]
    public function editAction(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $form = $this->createForm(
            OrderForm::class,
            $order,
            [
                'show_assets_question' => Order::TYPE_PF == $order->getType(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($order);
            $this->em->flush();

            // Remove documents previously added that aren't applicable to SUBTYPE_INTERIM_ORDER
            if (Order::SUBTYPE_INTERIM_ORDER === $order->getSubType()) {
                foreach ($order->getDocuments() as $document) {
                    $documentType = $document->getType();
                    if (Document::TYPE_COURT_ORDER !== $documentType && Document::TYPE_ADDITIONAL !== $documentType) {
                        try {
                            $this->documentService->deleteDocumentById($document->getId());
                        } catch (\Exception $e) {
                            $this->get('logger')->error($e->getMessage());
                            $this->addFlash('error', 'Non applicable document could not be removed from order.');
                        }
                    }
                }
            }

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        return $this->render('Order/edit.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/order/{orderId}/summary', name: 'order-summary')]
    public function summaryAction(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        // nothing answered -> go to step 1
        if (
            empty($order->getHasAssetsAboveThreshold())
            && empty($order->getSubType())
            && empty($order->getAppointmentType())
        ) {
            return $this->redirectToRoute('order-edit', ['orderId' => $order->getId()]);
        }

        $showCOUpload = $request->query->get('showCOUpload') ?? true;

        return $this->render('Order/summary.html.twig', [
            'order' => $order,
            'showCOUpload' => $showCOUpload,
        ]);
    }

    #[Route(path: '/order/{orderId}/declaration', name: 'order-declaration')]
    public function declarationAction(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $form = $this->createForm(DeclarationForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->orderService->serve($order);
                $client = $order->getClient();
                $request->getSession()->getFlashBag()->add('success',
                    [
                        'title' => 'order.served.title',
                        'clientName' => $client->getClientName(),
                        'caseNumber' => $client->getCaseNumber(),
                        'orderType' => $order->getType().'-success',
                    ]
                );
            } catch (\Exception $e) {
                $message = 'Order '.$orderId.' could not be served at the moment';
                if ($this->getParameter('kernel.debug')) {
                    $message .= '.Details (only on dev mode): '.$e;
                }
                $request->getSession()->getFlashBag()->add('error',
                    [
                        'body' => $message,
                        'orderType' => $order->getType().'-error',
                    ]);
            }

            return $this->redirectToRoute('case-list');
        }

        return $this->render('Order/declaration.html.twig', [
            'isServiceAvailable' => $this->orderService->isAvailable(),
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/order/{orderId}/upload', name: 'upload-order')]
    public function uploadOrder(int $orderId): Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        return $this->render('Order/upload.html.twig', ['order' => $order]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/order/{orderId}/process-order-doc', name: 'process-order-doc', methods: ['POST'])]
    public function processOrderDocument(Request $request, int $orderId): Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);
        /** @var UploadedFile $file */
        $file = $request->files->get('court-order');

        $document = new Document($order, Document::TYPE_COURT_ORDER);
        $document->setFile($file);

        if ($document->isWordDocument()) {
            try {
                $order = $this->orderService->hydrateOrderFromDocument($file, $order);
            } catch (WrongCaseNumberException $e) {
                return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            $this->em->persist($order);
            $this->em->flush();
        }

        try {
            $requestId = $request->headers->get('x-request-id') ?? 'test';
            $uploadDocResponse = $this->documentService->persistAndUploadDocument($order, $document, $file, $requestId);
        } catch (\Throwable $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $partial = false;

        if (!$order->isOrderValid()) {
            $flashMessage = <<<MESSAGE
The order was uploaded successfully.  We could not get all the information we need from the document.
Please enter some details below about the order
MESSAGE;

            $this->addFlash('success', $flashMessage);
            $partial = true;
        } else {
            $this->addFlash('success', 'The order was uploaded successfully.');
        }

        return new JsonResponse(
            [
                'success' => true,
                'partial' => $partial,
                'orderId' => $order->getId(),
                'documentId' => $uploadDocResponse['id'],
            ]
        );
    }

    #[Route(path: '/order/{orderId}/confirm-order-details', name: 'confirm-order-details', methods: ['GET', 'POST'])]
    public function confirmOrderDetails(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        if ($order->isOrderValid()) {
            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        $form = $this->createForm(
            ConfirmOrderDetailsForm::class,
            $order,
            [
                'show_assets_question' => Order::TYPE_PF == $order->getType() && null === $order->getHasAssetsAboveThreshold(),
                'show_subType_question' => null === $order->getSubType(),
                'show_appointmentType_question' => null === $order->getAppointmentType(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($order);
            $this->em->flush();

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId(), 'showCOUpload' => false]);
        }

        return $this->render(
            'Order/confirm-details.html.twig',
            ['order' => $order, 'form' => $form->createView()]
        );
    }

    #[Route(path: '/order/{orderId}/summary-served', name: 'served-order-summary', methods: ['GET'])]
    public function servedOrderSummary(Request $request, int $orderId): RedirectResponse|Response
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        if (!$order->getServedAt()) {
            return $this->redirectToRoute('order-summary', ['orderId' => $orderId]);
        }

        return $this->render(
            'Order/summary-served.html.twig',
            ['order' => $order]
        );
    }
}
