<?php

declare(strict_types=1);

namespace App\Service\Stats;

use App\Repository\OrderRepository;
use App\Service\Stats\Model\OrderMadePeriodStat;
use App\Service\Stats\Model\Stats;
use DateTime;

class Assembler
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function assembleOrderMadePeriodStats(string $orderStatus, ?DateTime $now = null)
    {
        $stats = new Stats($orderStatus);
        $now = $now ?: new DateTime('today');

        $currentYear = intval($now->format('Y'));

        foreach (range(2018, $currentYear) as $year) {
            $from = new DateTime(sprintf('1 January %s', $year));
            $to = ($year === $currentYear) ? $now : new DateTime(sprintf('31 December %s', $year));

            $ordersCount = $this->orderRepository->getOrdersCountByMadeDatePeriods($from, $to, $orderStatus);
            $stat = (new OrderMadePeriodStat())
                ->setFrom($from)
                ->setTo($to)
                ->setNumberOfOrders($ordersCount);

            $stats->addOrderMadePeriodStat($stat);
        }

        if ($orderStatus === Stats::STAT_STATUS_SERVED) {
            $ordersCount = $this->orderRepository->getOrdersCountByMadeDatePeriods($now, $now, $orderStatus);
            $stat = (new OrderMadePeriodStat())
                ->setFrom($now)
                ->setTo($now)
                ->setNumberOfOrders($ordersCount);

            $stats->addOrderMadePeriodStat($stat);
        }

        return $stats;
    }
}
