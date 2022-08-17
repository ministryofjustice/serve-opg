<?php

declare(strict_types=1);

namespace tests\Service\Stats\Model;

use App\Service\Stats\Model\OrderMadePeriodStat;
use App\Service\Stats\Model\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    /**
     * @test
     */
    public function getTotalOrders()
    {
        $sut = (new Stats())
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setNumberOfOrders(15)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setNumberOfOrders(5)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setNumberOfOrders(10)
            );

        self::assertEquals(30, $sut->getTotalOrdersCount());;
    }

    /**
     * @test
     * @dataProvider statusProvider
     */
    public function getDescription(string $statStatus, string $expectedDescription)
    {
        $sut = new Stats($statStatus);
        self::assertEquals($expectedDescription, $sut->getDescription());
    }

    public function statusProvider()
    {
        return [
            'To do' => [Stats::STAT_STATUS_TO_DO, 'Total court order backlog'],
            'Served' => [Stats::STAT_STATUS_SERVED, 'Total orders served'],
        ];
    }
}
