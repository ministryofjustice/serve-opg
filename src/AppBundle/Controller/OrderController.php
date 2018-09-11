<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPa;
use AppBundle\Entity\User;
use AppBundle\Form\DeclarationForm;
use AppBundle\Form\OrderForm;
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
     * OrderController constructor.
     * @param EntityManager $em
     * @param OrderService $orderService
     */
    public function __construct(EntityManager $em, OrderService $orderService)
    {
        $this->em = $em;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/order/{orderId}/edit", name="order-edit")
     */
    public function editAction(Request $request, $orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);
        /** @var $order Order */
        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }

        $form = $this->createForm(OrderForm::class, $order, [
            'show_assets_question' => $order->getType() == Order::TYPE_PA
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush($order);

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
        $order = $this->em->getRepository(Order::class)->find($orderId);
        /** @var $order Order */
        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }
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
        $order = $this->em->getRepository(Order::class)->find($orderId);
        /** @var $order Order */
        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }

        $form = $this->createForm(DeclarationForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(OrderService::class)->serve($order);

            $request->getSession()->getFlashBag()->add('notice', 'Order served to OPG');

            return $this->redirectToRoute('case-list');
        }

        return $this->render('AppBundle:Order:declaration.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);

    }
}