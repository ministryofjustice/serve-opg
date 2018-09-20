<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPf;
use Doctrine\ORM\EntityManager;

class CsvImporterService
{
    private static $orderTypeMap = [
        'hw' => OrderHw::class,
        'pf' => OrderPf::class,
    ];

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
     * @param ClientService $clientService
     * @param OrderService $orderService
     */
    public function __construct(ClientService $clientService, OrderService $orderService)
    {
        $this->clientService = $clientService;
        $this->orderService = $orderService;
    }

    /**
     * @param array $filePath file CSV with keys: Case, ClientName, OrderType, IssuedAt
     *
     * @return integer added columns
     */
    public function importFile($filePath)
    {
        $csvToArray = new CsvToArray($filePath, [
            'Case',
            'ClientName',
            'OrderType',
            'IssuedAt',
        ], true);
        $rows = $csvToArray->getData();

        $count = 0;
        foreach ($rows as $row) {
            $this->importSingleRow($row);
            $count++;
        }

        return $count;
    }

    /**
     * @param array $row
     *
     * @return Order
     */
    private function importSingleRow(array $row)
    {
        // client
        $client = $this->clientService->upsert($row['Case'], $row['ClientName']);

        // order
        $issuedAt = new \DateTime($row['IssuedAt']);
        $orderType = self::$orderTypeMap[$row['OrderType']] ?? null;
        if (empty($orderType)) {
            throw new \RuntimeException("Invalid order type:" . $row['OrderType']);
        }

        return $this->orderService->upsert($client, $orderType, $issuedAt);
    }

}
