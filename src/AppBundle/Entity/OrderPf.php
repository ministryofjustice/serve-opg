<?php

namespace AppBundle\Entity;

class OrderPf extends Order
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_PA;
    }
}