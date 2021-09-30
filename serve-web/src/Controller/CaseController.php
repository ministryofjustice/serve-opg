<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Security\LoginAttempts\Checker;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case")
 */
class CaseController extends AbstractController
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

        return $this->render('Case/index.html.twig', [
            'orders' => $this->orderRepo->getOrders($filters, $limit),
            'filters' => $filters,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount(['type' => 'pending'] + $filters),
                'served' => $this->orderRepo->getOrdersCount(['type' => 'served'] + $filters),
            ],
            'toDoStats' => [
                'totalOrders' => [
                    'amount' => '6526',
                    'description' => 'Total court order backlog'
                ],
                'select' => [
                    'label' => 'Show backlog by',
                    'options' => [
                        ['value' => 'year_breakdown', 'description' => 'Year Breakdown'],
                        ['value' => 'order_type', 'description' => 'Order Type'],
                        ['value' => 'order_status', 'description' => 'Order Status'],
                    ]
                ],
                'breakdownItems' => [
                    ['numberOfOrders' => '3456', 'dateFrom' => new DateTime('1 January 2018'), 'dateTo' => new DateTime('31 December 2018')],
                    ['numberOfOrders' => '1447', 'dateFrom' => new DateTime('1 January 2019'), 'dateTo' => new DateTime('31 December 2019')],
                    ['numberOfOrders' => '2497', 'dateFrom' => new DateTime('1 January 2020'), 'dateTo' => new DateTime('31 December 2020')],
                    ['numberOfOrders' => '3456', 'dateFrom' => new DateTime('1 January 2021'), 'dateTo' => new DateTime('now')],
                ]
            ],
            'servedStats' => [
                'totalOrders' => [
                    'amount' => '27568',
                    'description' => 'Total orders served'
                ],
                'select' => [
                    'label' => 'Show served Court Orders by',
                    'options' => [
                        ['value' => 'year_breakdown', 'description' => 'Year Breakdown'],
                        ['value' => 'order_type', 'description' => 'Order Type'],
                        ['value' => 'order_status', 'description' => 'Order Status'],
                    ]
                ],
                'breakdownItems' => [
                    ['numberOfOrders' => '6789', 'dateFrom' => new DateTime('1 January 2018'), 'dateTo' => new DateTime('31 December 2018')],
                    ['numberOfOrders' => '9221', 'dateFrom' => new DateTime('1 January 2019'), 'dateTo' => new DateTime('31 December 2019')],
                    ['numberOfOrders' => '8132', 'dateFrom' => new DateTime('1 January 2020'), 'dateTo' => new DateTime('31 December 2020')],
                    ['numberOfOrders' => '3456', 'dateFrom' => new DateTime('1 January 2021'), 'dateTo' => new DateTime('now')],
                    ['numberOfOrders' => '101'],
                ]
            ]
        ]);
    }
}
