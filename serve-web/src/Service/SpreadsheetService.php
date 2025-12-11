<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shuchkin\SimpleXLSX;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpreadsheetService
{
    /** @var array<int, array<string,int>> $removeCases */
    private array $removeCases = [];

    /** @var array<int, array<string, mixed>> $removeCases */
    private array $skippedCases = [];

    public function __construct(
       private readonly ClientService $clientService,
       private readonly OrderService $orderService,
       private readonly EntityManagerInterface $em,
       private readonly LoggerInterface $logger
    ) {}

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
        $csvColumns = [
            'Case',
            'Forename',
            'Surname',
            'Ord Type',
            'Made Date',
            'Issue Date',
            'Order No',
        ];

        $rows = $this->buildRowsArray($fileType, $path, $csvColumns);

        if (is_null($rows)) {
            return 0;
        }

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

    public function processDeletionsFile(UploadedFile $file): array
    {
        $fileType = $file->getClientMimeType();
        $path = $file->getPathname();
        $csvColumns = [
            'Case number',
            'Court order',
            'Client Name',
            'Order Made Date',
            'Order Issue Date',
        ];

        $rows = $this->buildRowsArray($fileType, $path, $csvColumns);

        $lastCaseNumber = 0;
        $lastOrderNumber = 0;
        $orderIds = [];
        foreach ($rows as $row) {
            $caseNumber = $row['Case number'];
            $orderNumber = (int) $row['Court order'];

            // Skip over any duplicate rows within the spreadsheet, as they will have been found via previous rows
            if ($lastCaseNumber === $caseNumber && $lastOrderNumber === $orderNumber) {
                continue;
            }

            if($lastCaseNumber !== $caseNumber || $lastOrderNumber !== $orderNumber) {
                // reset order ids for new case number/order number
                $orderIds = [];
            }

            $client = $this->clientService->findClientByCaseNumber($caseNumber);
            if (empty($client)) {
                $this->skippedCases[] = [
                    'caseNumber' => $caseNumber,
                    'clientId' => null,
                    'orders' => null,
                    'reason' => 'Unable to find any clients associated with casenumber',
                ];
                continue;
            }
            $orders = $this->orderService->findPendingOrdersByClient($client);

            if (count($orders) >= 2) {
                for ($i = 0; $i < count($orders); $i++) {
                    /** @var Order $order */
                    $order = $orders[$i];
                    if ((int) $order->getOrderNumber() !== $orderNumber) {
                        $this->logger->warning(sprintf(
                            'Retrieved Order: %d and Order to remove: %d, do not match skipping order',
                            $order->getOrderNumber(),
                            $orderNumber
                        ));
                        continue;
                    }

                    $lastOrderNumber = $order->getOrderNumber();
                    $orderIds[] = $order->getId();
                }

                if (count($orderIds) >= 1) {
                    $this->buildRemovalProcessedArrays($client, $orderIds);
                }
            } elseif (count($orders) === 1) {
                /** @var Order $order */
                $order = $orders[0];
                if ((int) $order->getOrderNumber() !== $orderNumber) {
                    $this->logger->warning(sprintf(
                        'Retrieved Order: %d and Order to remove: %d, do not match skipping row',
                        $order->getOrderNumber(),
                        $orderNumber
                    ));
                    continue;
                }

                $lastOrderNumber = $order->getOrderNumber();
                $orderIds = [$order->getId()];

                $this->buildRemovalProcessedArrays($client, $orderIds);
            } else {
                $this->skippedCases[] = [
                    'caseNumber' => $caseNumber,
                    'clientId' => null,
                    'orders' => null,
                    'reason' => sprintf(
                        'Unable to find any pending orders associated with client id: %d',
                        $client->getId()
                    ),
                ];
            }

            $lastCaseNumber = $caseNumber;
        }

        return ['removeCases' => $this->removeCases, 'skippedCases' => $this->skippedCases];
    }

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

    private function buildRowsArray(string $fileType, string $path, array $csvColumns): ?array
    {
        switch ($fileType) {
            case 'text/csv':
                $csvToArray = new CsvToArray($path, $csvColumns, true);
                return $csvToArray->getData();

            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                $xlsx = SimpleXLSX::parse($path);

                $header_values = $rows = [];
                foreach ($xlsx->rows() as $k => $r) {
                    if (0 === $k) {
                        $header_values = $r;
                        continue;
                    }
                    $rows[] = array_combine($header_values, $r);
                }

                if (!$xlsx->success()) {
                    $this->logger->error('Error parsing XLSX file: '.$xlsx->error());
                    return null;
                } else {
                    return $rows;
                }
            default:
                $this->logger->error(sprintf('Unsupported file type %s. Did not match CSV or XLXS', $fileType));

                return null;
        }
    }

    private function buildRemovalProcessedArrays(Client $client, array $orders): void
    {
        $caseNumber = $client->getCaseNumber();
        $clientId = $client->getId();

        if (count($orders) >= 1) {
            $this->removeCases[] = [
                'caseNumber' => $caseNumber,
                'clientId' => $clientId,
                'orders' => $orders,
            ];
        } else {
            $this->skippedCases[] = [
                'caseNumber' => $caseNumber,
                'clientId' => $clientId,
                'orders' => null,
                'reason' => sprintf('No order(s) associated with client id: %d', $client->getId()),
            ];
        }
    }
}
