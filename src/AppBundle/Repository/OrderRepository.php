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
    public function getOrdersCount(array $filters)
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
        $config = $this->_em->getConfiguration();
        $config->addCustomNumericFunction('CAST', 'Common\Query\Cast');

        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select("o, c")
            ->select("
                (
                    CASE WHEN (o.servedAt IS NULL) THEN 
                        (CAST(CONCAT(TO_CHAR(issued_at, 'YYYYMMDD')) AS INTEGER))
                    ELSE 
                        (CAST(CONCAT('-', TO_CHAR(served_at, 'YYYYMMDD')) AS INTEGER))
                    END
                ) AS custom_ordering
            ")
            ->leftJoin('o.client', 'c')
            ->setMaxResults($maxResults)
            ->orderBy('o.custom_ordering', 'ASC');


//        SELECT o.id AS order_id, c.id AS client_id, o.issued_at, o.served_at,
//                (CASE
//                    WHEN (o.served_at IS NULL) THEN
//                        CAST(CONCAT(to_char(issued_at, 'YYYYMMDD')) AS INTEGER)
//                    ELSE
//                        CAST(CONCAT('-', to_char(served_at, 'YYYYMMDD')) AS INTEGER)
//                 END
//                ) as custom_ordering
//                 FROM dc_order o, client c where o.client_id = c.id
//                 ORDER BY custom_ordering ASC');

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function applyFilters(QueryBuilder $qb, array $filters)
    {
        if ($filters['type'] == 'pending') {
            $qb->where('o.servedAt IS NULL');
        } elseif ($filters['type'] == 'served') {
            $qb->where('o.servedAt IS NOT NULL');
        }

        if ($filters['q'] ?? false) {
            $qb->andWhere('c.caseNumber = :cn')
            ->setParameter('cn', $filters['q']);
        }
    }
}
