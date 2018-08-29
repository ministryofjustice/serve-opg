<?php

namespace AppBundle\Entity;

class OrderPa extends Order
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_PA;
    }
}