<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/case')]
class CaseController extends AbstractController
{
    private OrderRepository $orderRepo;

    /**
     * UserController constructor.
     */
    public function __construct(EntityManager $em)
    {
        /** @var OrderRepository $repo */
        $repo = $em->getRepository(Order::class);

        $this->orderRepo = $repo;
    }

    #[Route(path: '', name: 'case-list')]
    public function indexAction(Request $request): Response
    {
        $filters = [
            'type' => $request->get('type', 'pending'),
            'q' => $request->get('q', ''),
        ];

        return $this->render('Case/index.html.twig', [
            'orders' => iterator_to_array($this->orderRepo->getOrders($filters, limit: 50, asArray: false)),
            'filters' => $filters,
            'counts' => [
                'pending' => $this->orderRepo->getOrdersCount(['type' => 'pending'] + $filters),
                'served' => $this->orderRepo->getOrdersCount(['type' => 'served'] + $filters),
            ],
        ]);
    }
}
