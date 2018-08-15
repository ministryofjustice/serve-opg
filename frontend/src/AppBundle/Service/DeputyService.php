<?php

namespace AppBundle\Service;

use AppBundle\Entity\Deputy;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTypeHw;
use AppBundle\Entity\OrderTypePa;
use Doctrine\ORM\EntityManager;

class DeputyService
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
    public function createDeputy(Deputy $deputy, Order $order)
    {
        switch($deputy->getOrderType()) {
            case Order::TYPE_PROPERTY_AFFAIRS:
                $orderType = $order->getTypesByOrderType(Order::TYPE_PROPERTY_AFFAIRS);
                $deputy->addOrderType($orderType);
                break;

            case Order::TYPE_HEALTH_WELFARE:
                $orderType = $order->getTypesByOrderType(Order::TYPE_HEALTH_WELFARE);
                $deputy->addOrderType($orderType);
                break;

            case Order::TYPE_BOTH:
                $deputy->addOrderType(new OrderTypePa());
                $deputy->addOrderType(new OrderTypeHw());
                break;
            default:
                throw new \Exception('Unable to create deputy: Order type not known');
        }
    }


}
