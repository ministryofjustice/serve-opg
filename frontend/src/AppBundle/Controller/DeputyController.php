<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Form\DeputyType;
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
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/case/order/{orderId}/deputy/add", name="deputy-add")
     */
    public function addAction(Request $request, $orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        $deputy = new Deputy($order);

        $form = $this->createForm(DeputyType::class, $deputy);
        $form->handleRequest($request);

        $buttonClicked = $form->getClickedButton();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($deputy);
            $this->em->flush($deputy);

            if ($buttonClicked->getName() == 'saveAndAddAnother') {
                return $this->redirectToRoute('deputy-add', ['orderId' => $order->getId()]);
            } else {
                return $this->redirectToRoute('document-add', ['orderId' => $order->getId()]);
            }
        }

        return $this->render('AppBundle:Deputy:add.html.twig', [
            'client' => $order->getClient(),
            'order' => $order,
            'form'=>$form->createView()
        ]);
    }
}
