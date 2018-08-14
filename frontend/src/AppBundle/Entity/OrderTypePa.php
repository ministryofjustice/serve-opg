<?php

namespace AppBundle\Entity;

class OrderTypePa extends OrderType
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_PROPERTY_AFFAIRS;
    }
}