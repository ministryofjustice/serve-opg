<?php

declare(strict_types=1);

namespace App\Service\Stats\Model;


class Stats
{
    const STAT_STATUS_TO_DO = 'pending';
    const STAT_STATUS_SERVED = 'served';

    /** @var []OrderMadePeriodStat */
    private array $orderMadePeriodStats = [];

    private string $status;

    public function __construct(string $status = self::STAT_STATUS_TO_DO)
    {
        $this->status = $status;
    }

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
        return new Filter($this->status);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->status === Stats::STAT_STATUS_TO_DO ? 'Total court order backlog' : 'Total orders served';
    }

    public function getTotalOrdersCount(): int
    {
        $total = 0;

        foreach ($this->orderMadePeriodStats as $stat) {
            $total += $stat->getNumberOfOrders();
        }

        return $total;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
