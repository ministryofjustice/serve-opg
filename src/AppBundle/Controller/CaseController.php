<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Repository\OrderRepository;
use AppBundle\Service\Security\LoginAttempts\Checker;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/case")
 */
class CaseController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var OrderRepository
     */
    private $orderRepo;

    /**
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->orderRepo = $em->getRepository(Order::class);
    }

    /**
     * @Route("", name="case-list")
     */
    public function indexAction(Request $request)
    {


        $limit = 50;

        $filters = [
            'type' => $request->get('type', 'pending'),
            'q' => $request->get('q', ''),
        ];

        return $this->render('AppBundle:Case:index.html.twig', [
            'orders' => $this->orderRepo->getOrders($filters, $limit),
            'filters' => $filters,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount(['type'=>'pending'] + $filters),
                'served' => $this->orderRepo->getOrdersCount(['type'=>'served'] + $filters),
            ]
        ]);
    }

}