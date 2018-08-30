<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
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
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("", name="case-list")
     */
    public function indexAction(Request $request)
    {
        $qb = $this->em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o,c')
            ->leftJoin('o.client', 'c');

        $filter = $request->get('filter', 'pending');
        if ($filter == 'pending') {
            $qb->where('o.servedAt IS NULL');
        } else {
            $qb->where('o.servedAt IS NOT NULL');
        }
        $orders = $qb->getQuery()->getResult();

        return $this->render('AppBundle:Case:index.html.twig', [
            'orders' => $orders
        ]);
    }

}