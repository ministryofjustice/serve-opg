<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/order")
 */
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
        return $this->render('AppBundle:Order:add.html.twig', [
            'client' => $this->em->getRepository(Client::class)->find($clientId)
        ]);
    }
}