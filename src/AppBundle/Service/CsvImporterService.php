<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class CsvImporterService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * CsvImporterService constructor.
     * @param EntityManager $em
     * @param ClientService $clientService
     * @param OrderService $orderService
     */
    public function __construct(EntityManager $em, ClientService $clientService, OrderService $orderService)
    {
        $this->em = $em;
        $this->clientService = $clientService;
        $this->orderService = $orderService;
    }

    /**
     * @param array $row with keys Case, ClientName, OrderType, IssuedAt
     *
     * @return \AppBundle\Entity\Order
     */
    public function import(array $row)
    {
        $wasCreated = null;

        $client = $this->clientService->upsert($row['Case'], $row['ClientName']);
        $issuedAt = new \DateTime($row['IssuedAt']);
        return $this->orderService->upsert($client, $row['OrderType'], $issuedAt, $wasCreated);
    }

}
