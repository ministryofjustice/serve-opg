<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Order;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
{
    /**
     * @param string $filter pending|served
     *
     * @return integer
     */
    public function getOrdersCount($filter)
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('COUNT(o)');

        $this->applyFilter($qb, $filter);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $filter pending|served
     *
     * @return Order[]
     */
    public function getOrders($filter)
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o,c')
            ->leftJoin('o.client', 'c');

        $this->applyFilter($qb, $filter);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string $filter pending|served
     */
    private function applyFilter(QueryBuilder $qb, $filter)
    {
        if ($filter == 'pending') {
            $qb->where('o.servedAt IS NULL');
        } else if ($filter == 'served') {
            $qb->where('o.servedAt IS NOT NULL');
        }
    }

}