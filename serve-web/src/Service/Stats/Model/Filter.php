<?php

declare(strict_types=1);

namespace App\Service\Stats\Model;


class Filter
{
    private string $statStatus;

    public function __construct(string $statStatus)
    {
        $this->statStatus = $statStatus;
    }

    public function getLabel(): string
    {
        return $this->statStatus === Stats::STAT_STATUS_TO_DO ? 'Show backlog by' : 'Show served Court Orders by';
    }

    public function getOptions(): array
    {
        return [
            ['value' => 'year_breakdown', 'description' => 'Year Breakdown'],
            ['value' => 'order_type', 'description' => 'Order Type'],
            ['value' => 'order_status', 'description' => 'Order Status'],
        ];
    }
}
