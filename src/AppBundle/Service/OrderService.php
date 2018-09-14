<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTypeHw;
use AppBundle\Entity\OrderPf;
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

    public function serve(Order $order)
    {
        if (!$order->readyToServe()) {
            throw new \RuntimeException("Order not ready to be served");
        }
        $order->setServedAt(new \DateTime());
        $this->em->flush($order);
    }

}