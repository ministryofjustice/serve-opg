<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Form\OrderForm;
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
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
     * @Route("/case/{orderId}/order", name="order")
     */
    public function addAction(Request $request, $orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId); /** @var $order Order */
        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }
        $form = $this->createForm(OrderForm::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush($order);

            return $this->redirectToRoute('deputy-add', ['orderId'=>$order->getId()]);
        }

        return $this->render('AppBundle:Order:index.html.twig', [
            'order' => $order,
            'form'=>$form->createView()
        ]);


    }
}