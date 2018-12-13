<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPf;
use AppBundle\Entity\User;
use AppBundle\Form\DeclarationForm;
use AppBundle\Form\OrderForm;
use AppBundle\Service\DocumentService;
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrderController extends Controller
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

        return $this->render('AppBundle:Order:edit.html.twig', [
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

        return $this->render('AppBundle:Order:summary.html.twig', [
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

        return $this->render('AppBundle:Order:declaration.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);
    }
}
