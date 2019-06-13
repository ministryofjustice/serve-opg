<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Order;
use App\Form\DeclarationForm;
use App\Form\OrderForm;
use App\Service\DocumentService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var DocumentService
     */
    private $documentService;

    /**
     * OrderController constructor.
     * @param EntityManager $em
     * @param OrderService $orderService
     */
    public function __construct(EntityManager $em, OrderService $orderService, DocumentService $documentService)
    {
        $this->em = $em;
        $this->orderService = $orderService;
        $this->documentService = $documentService;
    }

    /**
     * @Route("/order/{orderId}/edit", name="order-edit")
     */
    public function editAction(Request $request, $orderId)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $form = $this->createForm(OrderForm::class, $order, [
            'show_assets_question' => $order->getType() == Order::TYPE_PF
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->flush($order);

            // Remove documents previously added that aren't applicable to SUBTYPE_INTERIM_ORDER
            if ($order->getSubType() === order::SUBTYPE_INTERIM_ORDER) {
                foreach ($order->getDocuments() as $document) {
                    $documentType = $document->getType();
                    if ($documentType !== Document::TYPE_COURT_ORDER && $documentType !== Document::TYPE_ADDITIONAL) {
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
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/order/{orderId}/summary", name="order-summary")
     */
    public function summaryAction(Request $request, $orderId)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        // nothing answered -> go to step 1
        if (empty($order->getHasAssetsAboveThreshold())
            && empty($order->getSubType())
            && empty($order->getAppointmentType())
        ) {
            return $this->redirectToRoute('order-edit', ['orderId' => $order->getId()]);
        }

        return $this->render('Order/summary.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/order/{orderId}/declaration", name="order-declaration")
     */
    public function declarationAction(Request $request, $orderId)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $form = $this->createForm(DeclarationForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->orderService->serve($order);
                $client = $order->getClient();
                $request->getSession()->getFlashBag()->add('success',
                    array(
                        'title' => 'order.served.title',
                        'clientName' => $client->getClientName(),
                        'caseNumber' => $client->getCaseNumber(),
                        'orderType' =>$order->getType() . '-success'
                    )
                );
            } catch (\Exception $e) {
                $message = 'Order ' . $orderId . ' could not be served at the moment';
                if ($this->getParameter('kernel.debug')) {
                    $message .= '.Details (only on dev mode): '.$e;
                }
                $request->getSession()->getFlashBag()->add('error',
                    array(
                        'body' => $message,
                        'orderType' =>$order->getType() . '-error'
                    ));
            }


            return $this->redirectToRoute('case-list');
        }

        return $this->render('Order/declaration.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/order/{orderId}/upload", name="upload-order")
     */
    public function uploadOrder($orderId)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        return $this->render('Order/upload.html.twig', ['order' => $order]);
    }

    /**
     * @Route("/order/assert-doc-type", name="assert-doc-type", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function assertDocType(Request $request)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('court-order');
        $fileType = $file->getClientOriginalExtension();
        $acceptedTypes = ['doc', 'docx', 'tif', 'tiff'];

        if (!in_array($fileType, $acceptedTypes)) {
            return new Response(json_encode(['valid' => false]));
        }

        return new Response(json_encode(['valid' => true]));
    }

}
