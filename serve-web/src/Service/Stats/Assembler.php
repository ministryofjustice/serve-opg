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
    
    public function assembleOrderMadePeriodStats()
    {
        $stats = new Stats();

        $currentYear = intval(
            (new DateTime('today'))->format('Y')
        );

        foreach (range(2018, $currentYear) as $year) {
            $from = new DateTime(sprintf('1 January %s', $year));
            $to = $year === strval($currentYear) ? new DateTime('today') : new DateTime(sprintf('31 December %s', $year));

            $ordersCount = $this->orderRepository->getOrdersCountByMadeDatePeriods($from, $to);
            $stat = (new OrderMadePeriodStat())
                ->setFrom($from)
                ->setTo($to)
                ->setNumberOfOrders($ordersCount);

            $stats->addOrderMadePeriodStat($stat);
        }

        return $stats;
    }
}
