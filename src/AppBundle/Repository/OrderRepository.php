<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Order;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
{
    /**
     * @param array $filters
     *
     * @return integer
     */
    public function getOrdersCount($filters)
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->leftJoin('o.client', 'c')
        ;

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array $filters
     * @param integer $maxResults
     *
     * @return Order[]
     */
    public function getOrders(array $filters, $maxResults)
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o,c')
            ->leftJoin('o.client', 'c')
            ->setMaxResults($maxResults)
            ->orderBy('o.issuedAt', 'DESC')
        ;

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param aray $filters
     */
    private function applyFilters(QueryBuilder $qb, $filters)
    {
        if ($filters['type'] == 'pending') {
            $qb->where('o.servedAt IS NULL');
        } else if ( $filters['type'] == 'served') {
            $qb->where('o.servedAt IS NOT NULL');
        }

        if ($filters['q'] ?? false) {
            $qb->andWhere('c.caseNumber = :cn')
            ->setParameter('cn', $filters['q']);
        }
    }

}