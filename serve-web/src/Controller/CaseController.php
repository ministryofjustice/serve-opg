<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/case')]
class CaseController extends AbstractController
{
    private EntityManager $em;

    private ObjectRepository $orderRepo;

    /**
     * UserController constructor.
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->orderRepo = $em->getRepository(Order::class);
    }

    #[Route(path: '', name: 'case-list')]
    public function indexAction(Request $request): Response
    {
        $limit = 50;

        $filters = [
            'type' => $request->get('type', 'pending'),
            'q' => $request->get('q', ''),
        ];

        return $this->render('Case/index.html.twig', [
            'orders' => $this->orderRepo->getOrdersNotServedAndOrderReports($filters, $limit),
            'filters' => $filters,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount(['type' => 'pending'] + $filters),
                'served' => $this->orderRepo->getOrdersCount(['type' => 'served'] + $filters),
            ]
        ]);
    }
}
