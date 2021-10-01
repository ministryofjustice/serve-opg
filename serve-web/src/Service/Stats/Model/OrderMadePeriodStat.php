<?php

declare(strict_types=1);

namespace App\Service\Stats\Model;


use DateTime;

class OrderMadePeriodStat
{
    private int $numberOfOrders = 0;

    private ?DateTime $from;

    private ?DateTime $to;

    /**
     * @return int
     */
    public function getNumberOfOrders(): int
    {
        return $this->numberOfOrders;
    }

    /**
     * @param int $numberOfOrders
     * @return OrderMadePeriodStat
     */
    public function setNumberOfOrders(int $numberOfOrders): OrderMadePeriodStat
    {
        $this->numberOfOrders = $numberOfOrders;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getFrom(): ?DateTime
    {
        return $this->from;
    }

    /**
     * @param DateTime|null $from
     * @return OrderMadePeriodStat
     */
    public function setFrom(?DateTime $from): OrderMadePeriodStat
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getTo(): ?DateTime
    {
        return $this->to;
    }

    /**
     * @param DateTime|null $to
     * @return OrderMadePeriodStat
     */
    public function setTo(?DateTime $to): OrderMadePeriodStat
    {
        $this->to = $to;
        return $this;
    }
}
