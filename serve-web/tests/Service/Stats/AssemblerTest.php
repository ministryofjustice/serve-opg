<?php

declare(strict_types=1);

namespace tests\Service\Stats;

use App\Repository\OrderRepository;
use App\Service\Stats\Assembler;
use App\Service\Stats\Model\OrderMadePeriodStat;
use App\Service\Stats\Model\Stats;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class AssemblerTest extends TestCase
{
    /**
     * @test
     */
    public function assembleOrderMadePeriodStats_pending()
    {
        $now = new DateTime('7 October 2020');

        $from1 = new DateTime('1 January 2018');
        $from2 = new DateTime('1 January 2019');
        $from3 = new DateTime('1 January 2020');

        $to1 = new DateTime('31 December 2018');
        $to2 = new DateTime('31 December 2019');
        $to3 = $now;

        $orderStatus = Stats::STAT_STATUS_TO_DO;

        /** @var ObjectProphecy|OrderRepository $repo */
        $repo = self::prophesize(OrderRepository::class);
        $repo->getOrdersCountByMadeDatePeriods($from1, $to1, $orderStatus)->shouldBeCalled()->willReturn(25);
        $repo->getOrdersCountByMadeDatePeriods($from2, $to2, $orderStatus)->shouldBeCalled()->willReturn(75);
        $repo->getOrdersCountByMadeDatePeriods($from3, $to3, $orderStatus)->shouldBeCalled()->willReturn(100);

        $sut = new Assembler($repo->reveal());
        $actualResult = $sut->assembleOrderStats($orderStatus, $now);

        $expectedResult = (new Stats())
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($from1)
                    ->setTo($to1)
                    ->setNumberOfOrders(25)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($from2)
                    ->setTo($to2)
                    ->setNumberOfOrders(75)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($from3)
                    ->setTo($to3)
                    ->setNumberOfOrders(100)
            );

        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @test
     */
    public function assembleOrderMadePeriodStats_served()
    {
        $now = new DateTime('7 October 2019');

        $from1 = new DateTime('1 January 2018');
        $from2 = new DateTime('1 January 2019');

        $to1 = new DateTime('31 December 2018');
        $to2 = $now;

        $orderStatus = Stats::STAT_STATUS_SERVED;

        /** @var ObjectProphecy|OrderRepository $repo */
        $repo = self::prophesize(OrderRepository::class);
        $repo->getOrdersCountByMadeDatePeriods($from1, $to1, $orderStatus)->shouldBeCalled()->willReturn(25);
        $repo->getOrdersCountByMadeDatePeriods($from2, $to2, $orderStatus)->shouldBeCalled()->willReturn(75);
        $repo->getOrdersCountByMadeDatePeriods($now, $now, $orderStatus)->shouldBeCalled()->willReturn(5);

        $sut = new Assembler($repo->reveal());
        $actualResult = $sut->assembleOrderStats($orderStatus, $now);

        $expectedResult = (new Stats($orderStatus))
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($from1)
                    ->setTo($to1)
                    ->setNumberOfOrders(25)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($from2)
                    ->setTo($to2)
                    ->setNumberOfOrders(75)
            )
            ->addOrderMadePeriodStat(
                (new OrderMadePeriodStat())
                    ->setFrom($now)
                    ->setTo($now)
                    ->setNumberOfOrders(5)
            );

        self::assertEquals($expectedResult, $actualResult);
    }
}
