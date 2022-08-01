<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Security\LoginAttempts\Checker;
use App\Service\Stats\Assembler;
use App\Service\Stats\Model\Stats;
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
    private Assembler $assembler;

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

        if ($request->get('type') === 'pending') {
            if ($request->query->has('madeFrom')) {
                $filters += ['madeFrom' => $request->query->get('madeFrom')];
            }

            if ($request->query->has('madeTo')) {
                $filters += ['madeTo' => $request->query->get('madeTo')];
            }

            if (!$request->query->has('madeFrom') || !$request->query->has('madeTo')) {
                $filters += ['madeFrom' => (new DateTime())->format("Y-m-d")." 00:00:00"];
                $filters += ['madeTo' => (new DateTime())->format("Y-m-d")." 23:59:59"];
            }
        }

        if ($request->get('type') === 'served') {
            if ($request->query->has('startDate')) {
                $filters += ['startDate' => $request->query->get('startDate')];
            }

            if ($request->query->has('endDate')) {
                $filters += ['endDate' => $request->query->get('endDate')];
            }

            if (!$request->query->has('startDate') || !$request->query->has('endDate')) {
                $filters += ['startDate' => (new DateTime())->format("Y-m-d")." 00:00:00"];
                $filters += ['endDate' => (new DateTime())->format("Y-m-d")." 23:59:59"];
            }
        }
        
        $assembler = new Assembler($this->orderRepo);
        $toDoStats = $assembler->assembleOrderStats(Stats::STAT_STATUS_TO_DO);
        $servedStats = $assembler->assembleOrderStats(Stats::STAT_STATUS_SERVED);

        return $this->render('Case/index.html.twig', [
            'orders' => $this->orderRepo->getOrders($filters, $limit),
            'filters' => $filters,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount(['type' => 'pending'] + $filters),
                'served' => $this->orderRepo->getOrdersCount(['type' => 'served'] + $filters),
            ],
            'toDoStats' => $toDoStats,
            'servedStats' => $servedStats
        ]);
    }
}
