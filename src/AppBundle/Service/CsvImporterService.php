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
            'Case', // 8 digits. might end with a T
            'Forename',
            'Surname',
            'Corref', // HW / P2
            'Issue Date', //.e.g/ 15-Aug-2018
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
        $row = array_map('trim', $row);
        $case = strtoupper($row['Case']);
        $clientName = $row['Forename'].' '. $row['Surname']; //TODO different fields ?
        $orderType = strtolower($row['Corref']) =='hw' ? OrderHw::class : OrderPf::class;

        // client
        $client = $this->clientService->upsert($case, $clientName);

        // order
        $issuedAt = new \DateTime($row['Issue Date']);

        return $this->orderService->upsert($client, $orderType, $issuedAt);
    }
}
