<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Form\OrderType;
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
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/case/{clientId}/order/add", name="order-add")
     */
    public function addAction(Request $request, $clientId)
    {
        $client = $this->em->getRepository(Client::class)->find($clientId); /*@var $client Client*/
        // redirect to next step if one order was already created
        if (count($client->getOrders())) {
            return $this->redirectToRoute('deputy-add', ['orderId'=>$client->getOrders()->first()->getId()]);
        }
        $order = new Order($client);
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($order);
            $this->em->flush($order);

            return $this->redirectToRoute('deputy-add', ['orderId'=>$order->getId()]);
        }

        return $this->render('AppBundle:Order:add.html.twig', [
            'client' => $client,
            'form'=>$form->createView()
        ]);


    }
}