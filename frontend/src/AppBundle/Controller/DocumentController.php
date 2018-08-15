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

class DocumentController extends Controller
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
     * @Route("/case/order/{orderId}/document/add", name="document-add")
     */
    public function addAction(Request $request, $orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);

        return $this->render('AppBundle:Document:add.html.twig', [
            //'deputies' => $order->getAllDeputys()->toArray(),
            'client' => $order->getClient(),
            'order' => $order,
            //'form'=>$form->createView()
        ]);
    }
}
