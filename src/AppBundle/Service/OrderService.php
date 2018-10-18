<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
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
        $this->siriusService->serveOrder($order);

        $order->setServedAt(new \DateTime());
        $this->em->flush($order);
    }

    /**
     * @param integer $orderId
     *
     * @return Order
     */
    public function getOrderByIdIfNotServed($orderId)
    {
        /** @var $order Order */
        $order = $this->em->getRepository(Order::class)->find($orderId);

        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }
        if ($order->getServedAt()) {
            throw new AccessDeniedException('Cannot access an already served order');
        }

        return $order;
    }

    /**
     * @param Client $client
     * @param string $orderClass
     * @param \DateTime $issuedAt
     *
     * @return Order
     */
    public function upsert(Client $client, string $orderClass, \DateTime $madeAt, \DateTime $issuedAt)
    {
        /* @var $order Order */
        $order = $this->em->getRepository($orderClass)->findOneBy(['client' => $client]);
        if (!$order) {
            $order = new $orderClass($client, $madeAt, $issuedAt);
            $this->em->persist($order);
            $this->em->flush($client);
        }

        return $order;
    }

    /**
     * @param Order $order
     */
    public function emptyOrder(Order $order)
    {
        $orderId = $order->getId();
        $this->em->clear();
        $order = $this->em->getRepository(Order::class)->find($orderId);

        foreach ($order->getDeputies() as $deputy) {
            $this->em->remove($deputy);
        }
        foreach ($order->getDocuments() as $document) {
            $this->em->remove($document);
        }

        $order
            ->setServedAt(null)
            ->setSubType(null)
            ->setHasAssetsAboveThreshold(null)
            ->setAppointmentType(null);

        $this->em->flush();
    }
}
