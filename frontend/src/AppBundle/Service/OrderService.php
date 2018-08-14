<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTypeHw;
use AppBundle\Entity\OrderTypePa;
use Doctrine\ORM\EntityManager;

class OrderService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Order $order
     */
    public function createOrderTypes(Order $order)
    {
        switch($order->getType()) {
            case Order::TYPE_PROPERTY_AFFAIRS:
                $order->addType(new OrderTypePa());
                break;

            case Order::TYPE_HEALTH_WELFARE:
                $order->addType(new OrderTypeHw());
                break;

            case Order::TYPE_BOTH:
                $order->addType(new OrderTypePa());
                $order->addType(new OrderTypeHw());
        }
    }


}