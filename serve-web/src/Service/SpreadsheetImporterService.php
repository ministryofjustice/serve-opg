<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shuchkin\SimpleXLSX;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpreadsheetImporterService
{
    private ClientService $clientService;
    private OrderService $orderService;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(
        ClientService $clientService,
        OrderService $orderService,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * file with keys:
     * Case : 8 digits. might end with a T
     * Forename
     * Surname
     * Order Type: integer. 2 means HW order
     * IssuedAt e.g. 15-Aug-2018 or any format accepted by DateTime
     */
    public function importFile(UploadedFile $file): int
    {
        $fileType = $file->getClientMimeType();
        $path = $file->getPathname();
        switch ($fileType) {
            case ('text/csv'):
                $csvToArray = new CsvToArray($path, [
                    'Case',
                    'Forename',
                    'Surname',
                    'Ord Type',
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

            case ('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'):
                $xlsx = SimpleXLSX::parse($path);

                $header_values = $rows = [];
                foreach ( $xlsx->rows() as $k => $r ) {
                    if ( $k === 0 ) {
                        $header_values = $r;
                        continue;
                    }
                    $rows[] = array_combine( $header_values, $r );
                }

                $count = 0;
                if (!$xlsx->success()) {
                    $this->logger->error('Error parsing XLSX file: ' . $xlsx->error());
                } else {
                    foreach ($rows as $row) {
                        $this->importSingleRow($row);

                        if ($count % 25 === 0) {
                            $this->em->flush();
                            $this->em->clear();
                        }

                        $count++;
                    }
                }
                return $count;
            default:
                $this->logger->error(sprintf('Unsupported file type %s. Did not match CSV or XLXS', $fileType));
                return 0;
        }
    }

    private function importSingleRow(array $row): Order
    {
        $row = array_map('trim', $row);

        $case = strtoupper($row['Case']);
        $clientName = $row['Forename'].' '. $row['Surname']; //TODO different fields ?
        $orderType = $row['Ord Type'] == 2 ? OrderHw::class : OrderPf::class;

        // client
        $client = $this->clientService->upsert($case, $clientName);

        // order
        $issuedAt = new \DateTime($row['Issue Date']);
        $madeAt = new \DateTime($row['Made Date']);
        $orderNumber = $row['Order No'];

        return $this->orderService->upsert($client, $orderType, $madeAt, $issuedAt, $orderNumber);
    }
}
