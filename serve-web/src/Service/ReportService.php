<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\File;

class ReportService
{
    private EntityRepository $orderRepo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->orderRepo = $em->getRepository(Order::class);
    }

    /**
     * Generates a CSV file of served orders for the past 4 weeks.
     */
    public function generateCsv(): File
    {
        $endDate = new \DateTime('now');
        $startDate = (new \DateTime('now'))->modify('-4 weeks');

        $orders = $this->getOrders('served-last-4-weeks', $startDate, $endDate);

        $headers = ['DateIssued', 'DateMade', 'DateServed', 'CaseNumber', 'AppointmentType', 'OrderType'];

        $today = (new \DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/orders-served-$today.csv", 'w');

        fputcsv($file, $headers);

        foreach ($orders as $order) {
            fputcsv($file, [
                'DateIssued' => $order->getIssuedAt()->format('Y-m-d'),
                'DateMade' => $order->getMadeAt()->format('Y-m-d'),
                'DateServed' => $order->getServedAt()?->format('Y-m-d'),
                'CaseNumber' => $order->getClient()->getCaseNumber(),
                'AppointmentType' => $order->getAppointmentType(),
                'OrderType' => $order->getType(),
            ]);
        }

        fclose($file);

        return new File("/tmp/orders-served-$today.csv");
    }

    /**
     * Generates a CSV file of orders waiting to be served.
     */
    public function generateOrdersNotServedCsv(): File
    {
        $startDate = new \DateTime('2001-01-01 00:00:00');
        $endDate = (new \DateTime('now'))->modify('+1 days');

        $orders = $this->getOrders('pending', $startDate, $endDate);

        $headers = ['CaseNumber', 'OrderType', 'OrderNumber', 'ClientName', 'OrderMadeDate', 'OrderIssueDate', 'Status'];

        $today = (new \DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/all-orders-not-served-$today.csv", 'w');

        fputcsv($file, $headers);

        foreach ($orders as $order) {
            fputcsv($file, [
                'CaseNumber' => $order->getClient()->getCaseNumber(),
                'OrderType' => $order->getType(),
                'OrderNumber' => $order->getOrderNumber(),
                'ClientName' => $order->getClient()->getClientName(),
                'OrderMadeDate' => $order->getMadeAt()->format('Y-m-d'),
                'OrderIssueDate' => $order->getIssuedAt()->format('Y-m-d'),
                'Status' => 'READY TO SERVE' ? $order->readyToServe() : 'TO DO',
            ]);
        }

        fclose($file);

        return new File("/tmp/all-orders-not-served-$today.csv");
    }

    /**
     * Generates a CSV file of all served orders.
     */
    public function generateAllServedOrdersCsv(): File
    {
        $startDate = new \DateTime('2001-01-01 00:00:00');
        $endDate = (new \DateTime('now'))->modify('+1 days');
        $today = (new \DateTime('now'))->format('Y-m-d');

        $file = fopen("/tmp/all-served-orders-$today.csv", 'w');

        $headers = ['DateIssued', 'DateMade', 'CaseNumber', 'OrderType', 'OrderNumber', 'ClientName', 'OrderServedDate'];

        fputcsv($file, $headers);

        $orders = $this->getOrders('served', $startDate, $endDate);

        foreach ($orders as $order) {
            fputcsv($file, [
                'DateIssued' => $order['issued_at_7'],
                'DateMade' => $order['made_at_6'],
                'CaseNumber' => $order['case_number_13'],
                'OrderType' => $order['type_16'],
                'OrderNumber' => $order['order_number_11'],
                'ClientName' => $order['client_name_14'],
                'OrderServedDate' => $order['served_at_8'],
            ]);
        }

        fclose($file);

        return new File("/tmp/all-served-orders-$today.csv");
    }

    /**
     *  Get orders that have been served into Sirius.
     */
    public function getOrders(string $type, \DateTime $startDate, \DateTime $endDate): iterable
    {
        $formattedEndDate = $endDate->format('Y-m-d');
        $formattedStartDate = $startDate->format('Y-m-d');

        $filters = [
            'type' => $type,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
        ];

        if ('served' === $type) {
            return $this->orderRepo->getAllServedOrders($filters);
        } else {
            return $this->orderRepo->getOrdersNotServedAndOrderReports($filters);
        }
    }

    /**
     * @return false|resource
     */
    public function getCasesBeforeGoLive()
    {
        $orders = $this->orderRepo->getOrdersBeforeGoLive();

        $headers = ['DateCreated', 'DateServed', 'CaseNumber', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            if (null === $order->getServedAt()) {
                $ordersCsv[] = ['DateCreated' => $order->getCreatedAt()->format('Y-m-d'),
                    'DateServed' => 'Null',
                    'CaseNumber' => $order->getClient()->getCaseNumber(),
                    'OrderType' => $order->getType()];
            }
        }

        $file = fopen('/tmp/cases.csv', 'w');

        fputcsv($file, $headers);

        foreach ($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return $file;
    }
}
