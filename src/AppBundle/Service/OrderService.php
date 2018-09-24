<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderTypeHw;
use AppBundle\Entity\OrderPf;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SiriusService
     */
    private $siriusService;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, SiriusService $siriusService)
    {
        $this->em = $em;
        $this->siriusService = $siriusService;
    }

    public function serve(Order $order)
    {
        if (!$order->readyToServe()) {
            throw new \RuntimeException("Order not ready to be served");
        }

        // Make API call to Sirius
        try {
            var_dump($this->siriusService->serveOrder($order));
            exit;
        } catch (\Exception $e) {
            throw $e;
        }
//        $order->setServedAt(new \DateTime());
//        $this->em->flush($order);
    }

    /**
     * @param integer $orderId
     *
     * @return Order
     */
    public function getOrderByIdIfNotServed($orderId)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId); /** @var $order Order */

        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }
        if ($order->getServedAt()) {
            throw new AccessDeniedException('Cannot access an already served order');
        }

        return $order;
    }

}
