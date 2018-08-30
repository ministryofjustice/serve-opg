<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Repository\OrderRepository;
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
        $filter = $request->get('filter', 'pending');

        return $this->render('AppBundle:Case:index.html.twig', [
            'orders' => $this->orderRepo->getOrders($filter),
            'filter' => $filter,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount('pending'),
                'served' => $this->orderRepo->getOrdersCount('served'),
            ]
        ]);
    }

}