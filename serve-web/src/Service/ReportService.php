<?php

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\File\File;

class ReportService
{
    private OrderRepository $orderRepo;

    public function __construct(EntityManagerInterface $em)
    {
        /** @var OrderRepository $repo */
        $repo = $em->getRepository(Order::class);

        $this->orderRepo = $repo;
    }

    /**
     * Generates a CSV file of served orders for the past 4 weeks.
     */
    public function generateLast4WeeksCsv(): File
    {
        $endDate = new \DateTime('now');
        $startDate = (new \DateTime('now'))->modify('-4 weeks');

        $orders = $this->getFilteredOrders('served-last-4-weeks', $startDate, $endDate);

        $headers = ['DateIssued', 'DateMade', 'DateServed', 'CaseNumber', 'AppointmentType', 'OrderType'];

        $today = (new \DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/orders-served-$today.csv", 'w');

        fputcsv($file, $headers, escape: '');

        foreach ($orders as $order) {
            fputcsv($file, [
                'DateIssued' => $order['issuedAt']?->format('Y-m-d'),
                'DateMade' => $order['madeAt']?->format('Y-m-d'),
                'DateServed' => $order['servedAt']?->format('Y-m-d'),
                'CaseNumber' => $order['client']['caseNumber'],
                'AppointmentType' => $order['appointmentType'],
                'OrderType' => $order['type'],
            ], escape: '');
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

        $orders = $this->getFilteredOrders('pending', $startDate, $endDate, asArray: false);

        $headers = ['CaseNumber', 'OrderType', 'OrderNumber', 'ClientName', 'OrderMadeDate', 'OrderIssueDate', 'Status'];

        $today = (new \DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/all-orders-not-served-$today.csv", 'w');

        fputcsv($file, $headers, escape: '');

        /** @var Order $order */
        foreach ($orders as $order) {
            fputcsv($file, [
                'CaseNumber' => $order->getClient()->getCaseNumber(),
                'OrderType' => $order->getType(),
                'OrderNumber' => $order->getOrderNumber(),
                'ClientName' => $order->getClient()->getClientName(),
                'OrderMadeDate' => $order->getMadeAt()->format('Y-m-d'),
                'OrderIssueDate' => $order->getIssuedAt()->format('Y-m-d'),
                'Status' => $order->readyToServe() ? 'READY TO SERVE' : 'TO DO',
            ], escape: '');
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

        fputcsv($file, $headers, escape: '');

        $orders = $this->orderRepo->getServedOrders();

        /** @var Order $order */
        foreach ($orders as $order) {
            // How to get the order type?
            fputcsv($file, [
                'DateIssued' => $order['issuedAt']?->format('Y-m-d H:i:s'),
                'DateMade' => $order['madeAt']?->format('Y-m-d H:i:s'),
                'CaseNumber' => $order['caseNumber'],
//                'OrderType' => $order['type'],
                'OrderNumber' => $order['orderNumber'],
                'ClientName' => $order['clientName'],
                'OrderServedDate' => $order['servedAt']?->format('Y-m-d'),
            ], escape: '');
        }

        fclose($file);

        return new File("/tmp/all-served-orders-$today.csv");
    }

    /**
     * Get orders that have been served into Sirius.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getFilteredOrders(string $type, \DateTime $startDate, \DateTime $endDate, bool $asArray = true): \Traversable
    {
        $formattedEndDate = $endDate->format('Y-m-d');
        $formattedStartDate = $startDate->format('Y-m-d');

        $typeFilter = 'served';
        if ('pending' == $type) {
            $typeFilter = 'pending';
        }

        $filters = [
            'type' => $typeFilter,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
        ];

        return $this->orderRepo->getOrders($filters, asArray: $asArray);
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

        fputcsv($file, $headers, escape: '');

        foreach ($ordersCsv as $line) {
            fputcsv($file, $line, escape: '');
        }

        fclose($file);

        return $file;
    }
}
