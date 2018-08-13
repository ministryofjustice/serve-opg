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
 * @Route("/cases")
 */
class CaseController extends Controller
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
     * @Route("", name="cases")
     */
    public function dashboardAction(Request $request)
    {
        return $this->render('AppBundle:Case:dashboard.html.twig', [
            'clients' => $this->em->getRepository(Client::class)->findAll()
        ]);
    }

    /**
     * @Route("create-order/{clientId}", name="create-order")
     */
    public function createOrderAction(Request $request, $clientId)
    {
        return $this->render('AppBundle:Case:createOrder.html.twig', [
            'client' => $this->em->getRepository(Client::class)->find($clientId)
        ]);
    }
}