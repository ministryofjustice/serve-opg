<?php
namespace App\Repository;

use App\Entity\Order;
use App\Service\Stats\Model\Stats;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

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
    private function applyFilters(QueryBuilder $qb, array $filters)
    {
        if ($filters['type'] == 'pending') {
            $qb->andWhere('o.servedAt IS NULL');
        } elseif ($filters['type'] == 'served') {
            $qb->andWhere('o.servedAt IS NOT NULL');
        }

        if ($filters['q'] ?? false) {
            $qb->andWhere('UPPER(c.caseNumber) LIKE :cn')
            ->setParameter('cn', strtoupper(trim($filters['q'])));
        }
    }

    public function getOrdersBeforeGoLive()
    {
        $qb = $this->_em->getRepository(Order::class)
            ->createQueryBuilder("o")
            ->select("o")
            ->where("o.createdAt < '2019-03-11'")
            ->andWhere("o.servedAt IS NULL");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return int|mixed|string
     * @throws \Exception
     */
    public function getOrdersCountByMadeDatePeriods(DateTime $from, DateTime $to, string $orderStatus)
    {
        $from = new DateTime($from->format("Y-m-d")." 00:00:00");
        $to   = new DateTime($to->format("Y-m-d")." 23:59:59");

        $qb = $this->_em->getRepository(Order::class)->createQueryBuilder("o")
            ->select('COUNT(o)');

        if ($orderStatus === Stats::STAT_STATUS_TO_DO) {
            $qb->andWhere('o.madeAt BETWEEN :from AND :to');
        } elseif ($orderStatus === Stats::STAT_STATUS_SERVED) {
            $qb->andWhere('o.servedAt BETWEEN :from AND :to');
        } else {
            throw new InvalidArgumentException('Order status must be "pending" or "served"');
        }

        $qb->setParameter('from', $from )
            ->setParameter('to', $to);

        $this->applyFilters($qb, ['type' => $orderStatus]);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
