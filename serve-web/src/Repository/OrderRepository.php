<?php
namespace App\Repository;

use App\Entity\Order;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
{
    public function getOrdersCount(array $filters): mixed
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
    public function getOrders(array $filters, $maxResults): array
    {
        /**
         * If the order is served, we order using the inverse (-) servedBy date, otherwise we use the issued date.
         * Negative dates as a integer result in a custom ordering field allow different ordering on the two order tabs,
         * (served and pending)
         */
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select("o, c")
            ->addSelect("
                (
                    CASE WHEN (o.servedAt IS NULL) THEN
                        cast_as_integer(
                            to_date(o.issuedAt, 'YYYYMMDD')
                        )
                    ELSE
                        cast_as_integer(
                            CONCAT('-', to_date(o.servedAt, 'YYYYMMDD'))
                        )
                    END
                ) AS HIDDEN custom_ordering
            ")

            ->leftJoin('o.client', 'c')
            ->setMaxResults($maxResults)
            ->orderBy('custom_ordering', 'ASC');

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if ($filters['type'] == 'pending') {
            $qb->where('o.servedAt IS NULL');
        } elseif ($filters['type'] == 'served') {
            $qb->where('o.servedAt IS NOT NULL');
        }

        if ($filters['q'] ?? false) {
            $qb->andWhere('UPPER(c.caseNumber) LIKE :cn')
            ->setParameter('cn', strtoupper(trim($filters['q'])));
        }

        if (
            array_key_exists('startDate', $filters) &&
            array_key_exists('endDate', $filters)
        ) {
            if ($filters['type'] == 'served') {
                $qb->andWhere('o.servedAt >= :start AND o.servedAt <= :end')
                    ->setParameter('start', $filters['startDate'])
                    ->setParameter('end', $filters['endDate']);
            }
            else {
                $qb->andWhere('o.madeAt >= :start AND o.madeAt <= :end')
                    ->setParameter('start', $filters['startDate'])
                    ->setParameter('end', $filters['endDate']);
            }
        }
    }

    public function getOrdersBeforeGoLive(): mixed
    {

        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder("o")
            ->select("o")
            ->where("o.createdAt < '2019-03-11'")
            ->andWhere("o.servedAt IS NULL");

        return $qb->getQuery()->getResult();
    }
}
