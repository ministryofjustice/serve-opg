<?php

namespace AppBundle\Entity;

class OrderHw extends Order
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_HW;
    }
}