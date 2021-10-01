<?php

declare(strict_types=1);

namespace App\Service\Stats\Model;


class Stats
{
    /** @var array []OrderMadePeriodStats */
    private array $orderMadePeriodStats = [];

    private ?Filter $filter = null;

    private ?string $description = null;

    /**
     * @return array
     */
    public function getOrderMadePeriodStats(): array
    {
        return $this->orderMadePeriodStats;
    }

    /**
     * @param array $orderMadePeriodStats
     * @return Stats
     */
    public function setOrderMadePeriodStats(array $orderMadePeriodStats): Stats
    {
        $this->orderMadePeriodStats = $orderMadePeriodStats;
        return $this;
    }

    /**
     * @param OrderMadePeriodStat $reportingPeriodDetails
     * @return Stats
     */
    public function addOrderMadePeriodStat(OrderMadePeriodStat $reportingPeriodDetails): Stats
    {
        if (!in_array($reportingPeriodDetails, $this->orderMadePeriodStats)) {
            $this->orderMadePeriodStats[] = $reportingPeriodDetails;
        }

        return $this;
    }

    /**
     * @return Filter|null
     */
    public function getFilter(): ?Filter
    {
        return $this->filter;
    }

    /**
     * @param Filter|null $filter
     * @return Stats
     */
    public function setFilter(?Filter $filter): Stats
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Stats
     */
    public function setDescription(?string $description): Stats
    {
        $this->description = $description;
        return $this;
    }
}
