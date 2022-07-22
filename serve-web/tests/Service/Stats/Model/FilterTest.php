<?php

namespace tests\Service\Stats\Model;

use App\Service\Stats\Model\Filter;
use App\Service\Stats\Model\Stats;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusProvider
     */
    public function getLabel(string $statStatus, string $expectedLabel)
    {
        $sut = new Filter($statStatus);
        self::assertEquals($expectedLabel, $sut->getLabel());
    }

    public function statusProvider()
    {
        return [
            'To do' => [Stats::STAT_STATUS_TO_DO, 'Show backlog by'],
            'Served' => [Stats::STAT_STATUS_SERVED, 'Show served Court Orders by'],
        ];
    }
}
