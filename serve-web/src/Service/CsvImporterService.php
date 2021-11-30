<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

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
    private EntityManagerInterface $em;

    /**
     * CsvImporterService constructor.
     * @param ClientService $clientService
     * @param OrderService $orderService
     */
    public function __construct(ClientService $clientService, OrderService $orderService, EntityManagerInterface $em)
    {
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->em = $em;
    }

    /**
     * @param string $filePath file CSV with keys:
     * Case : 8 digits. might end with a T
     * Forename
     * Surname
     * Order Type: integer. 2 means HW order
     * IssuedAt e.g. 15-Aug-2018 or any format accepted by DateTime
     *
     * @return integer added columns
     */
    public function importFile(string $filePath)
    {
        $csvToArray = new CsvToArray($filePath, [
            'Case',
            'Forename',
            'Surname',
            'Order Type',
            'Made Date',
            'Issue Date',
            'Order No'
        ], true);
        $rows = $csvToArray->getData();

        $count = 0;
        foreach ($rows as $row) {
            $this->importSingleRow($row);

            if ($count % 25 === 0) {
                $this->em->flush();
                $this->em->clear();
            }

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
        $orderType = $row['Order Type'] == 2 ? OrderHw::class : OrderPf::class;

        // client
        $client = $this->clientService->upsert($case, $clientName);

        // order
        $issuedAt = new \DateTime($row['Issue Date']);
        $madeAt = new \DateTime($row['Made Date']);
        $orderNumber = $row['Order No'];

        return $this->orderService->upsert($client, $orderType, $madeAt, $issuedAt, $orderNumber);
    }
}
