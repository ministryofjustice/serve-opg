<?php

namespace AppBundle\Entity;

class OrderTypeHw extends OrderType
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_HEALTH_WELFARE;
    }
}