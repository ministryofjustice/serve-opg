<?php

namespace App\Repository;

use App\Common\Query\QueryPager;
use App\Entity\Order;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
{
    private function createOrdersQueryBuilder(array $filters): QueryBuilder
    {
        /**
         * If the order is served, we order using the inverse (-) servedBy date, otherwise we use the issued date.
         * Negative dates as a integer result in a custom ordering field allow different ordering on the two order tabs,
         * (served and pending).
         */
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o, c')
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
        ->orderBy('custom_ordering', 'ASC');

        $this->applyFilters($qb, $filters);

        return $qb;
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if ('pending' == $filters['type']) {
            $qb->where('o.servedAt IS NULL');
        } elseif ('served' == $filters['type']) {
            $qb->where('o.servedAt IS NOT NULL');
        }

        if ($filters['q'] ?? false) {
            $qb->andWhere('UPPER(c.caseNumber) LIKE :cn')
            ->setParameter('cn', strtoupper(trim($filters['q'])));
        }

        if (
            array_key_exists('startDate', $filters)
            && array_key_exists('endDate', $filters)
        ) {
            if ('served' == $filters['type']) {
                $qb->andWhere('o.servedAt >= :start AND o.servedAt <= :end')
                    ->setParameter('start', $filters['startDate'])
                    ->setParameter('end', $filters['endDate']);
            } else {
                $qb->andWhere('o.madeAt >= :start AND o.madeAt <= :end')
                    ->setParameter('start', $filters['startDate'])
                    ->setParameter('end', $filters['endDate']);
            }
        }
    }

    public function getOrdersBeforeGoLive(): mixed
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('o')
            ->where("o.createdAt < '2019-03-11'")
            ->andWhere('o.servedAt IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function getOrdersCountQuery(array $filters): Query
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->leftJoin('o.client', 'c');

        $this->applyFilters($qb, $filters);

        return $qb->getQuery();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getOrdersCount(array $filters): int
    {
        /** @var int $count */
        $count = $this->getOrdersCountQuery($filters)->getSingleScalarResult();

        return $count;
    }

    /**
     * $filters will typically contain a "type" property specifying the type of orders to return, e.g. "pending", "served".
     *
     * @return \Traversable<array>
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getOrders(array $filters, int $limit = 0, bool $asArray = true): \Traversable
    {
        $countQuery = $this->getOrdersCountQuery($filters);

        // additional ordering ensures we get a consistent order for paging purposes
        $pageQuery = $this->createOrdersQueryBuilder($filters)
            ->orderBy('o.id', 'ASC')
            ->orderBy('c.id', 'ASC')
            ->getQuery();

        $pager = new QueryPager($countQuery, $pageQuery);

        return $pager->getRows(asArray: $asArray, limit: $limit);
    }

    public function deletePendingOrder(int $orderId): void
    {
        $orderRepo = $this->_em->getRepository(Order::class);
        $pendingOrder = $orderRepo->findOneBy(['id' => $orderId, 'servedAt' => null]);

        if ($pendingOrder) {
            $this->_em->remove($pendingOrder);
            $this->_em->flush();
        }
    }
}
