<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Service\DeputyService;
use AppBundle\Form\DeputyForm;
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DeputyController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeputyService
     */
    private $deputyService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * DeputyController constructor.
     * @param EntityManager $em
     * @param DeputyService $deputyService
     * @param OrderService $orderService
     */
    public function __construct(EntityManager $em, DeputyService $deputyService, OrderService $orderService)
    {
        $this->em = $em;
        $this->deputyService = $deputyService;
        $this->orderService = $orderService;
    }

    /**
     * @Route("/order/{orderId}/deputy/add", name="deputy-add")
     */
    public function addAction(Request $request, $orderId)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $deputy = new Deputy($order);

        $form = $this->createForm(DeputyForm::class, $deputy);
        $form->handleRequest($request);

        $buttonClicked = $form->getClickedButton();

        if ($form->isSubmitted() && $form->isValid()) {
            $order->addDeputy($deputy);
            $this->em->persist($deputy);
            $this->em->flush();

            if ($buttonClicked->getName() == 'saveAndAddAnother') {
                return $this->redirectToRoute('deputy-add', ['orderId' => $order->getId()]);
            } else {
                return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
            }
        }

        return $this->render('AppBundle:Deputy:add.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/case/order/{orderId}/deputy/edit/{deputyId}", name="deputy-edit")
     */
    public function editAction(Request $request, $orderId, $deputyId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        $deputy = $order->getDeputyById($deputyId);

        $form = $this->createForm(DeputyForm::class, $deputy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($deputy);
            $this->em->flush();

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
        }

        return $this->render('AppBundle:Deputy:add.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form' => $form->createView()
        ]);
    }
}
