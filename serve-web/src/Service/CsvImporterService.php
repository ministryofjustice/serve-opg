<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CsvImporterService
{
    private static array $orderTypeMap = [
        'hw' => OrderHw::class,
        'pf' => OrderPf::class,
    ];

    private ClientService $clientService;

    private OrderService $orderService;
    private EntityManagerInterface $em;

    public function __construct(ClientService $clientService, OrderService $orderService, EntityManagerInterface $em)
    {
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->em = $em;
    }

    /**
     * file CSV with keys:
     * Case : 8 digits. might end with a T
     * Forename
     * Surname
     * Order Type: integer. 2 means HW order
     * IssuedAt e.g. 15-Aug-2018 or any format accepted by DateTime
     */
    public function importFile(string $filePath): int
    {
        $csvToArray = new CsvToArray($filePath, [
            'Case',
            'Forename',
            'Surname',
            'Ord Type',
            'Made Date',
            'Issue Date',
            'Order No',
        ], true);
        $rows = $csvToArray->getData();

        $count = 0;
        foreach ($rows as $row) {
            $this->importSingleRow($row);

            if (0 === $count % 25) {
                $this->em->clear();
            }

            ++$count;
        }

        return $count;
    }

    /**
     * @throws \Exception
     */
    private function importSingleRow(array $row): Order
    {
        $row = array_map('trim', $row);

        $case = strtoupper($row['Case']);
        $clientName = $row['Forename'].' '.$row['Surname']; // TODO different fields ?
        $orderType = 2 == $row['Ord Type'] ? OrderHw::class : OrderPf::class;

        // client
        $client = $this->clientService->upsert($case, $clientName);

        // order
        $issuedAt = new \DateTime($row['Issue Date']);
        $madeAt = new \DateTime($row['Made Date']);
        $orderNumber = $row['Order No'];

        return $this->orderService->upsert($client, $orderType, $madeAt, $issuedAt, $orderNumber);
    }
}
