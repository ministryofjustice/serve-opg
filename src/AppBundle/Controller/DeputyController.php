<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Service\DeputyService;
use AppBundle\Form\DeputyForm;
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
     * DeputyController constructor
     * .
     * @param EntityManager $em
     * @param DeputyService $deputyService
     */
    public function __construct(EntityManager $em, DeputyService $deputyService)
    {
        $this->em = $em;
        $this->deputyService = $deputyService;

    }

    /**
     * @Route("/case/order/{orderId}/deputy/add", name="deputy-add")
     */
    public function addAction(Request $request, $orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

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
            'form'=>$form->createView()
        ]);
    }
}
