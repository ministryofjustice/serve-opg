<?php


namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DoctrineExtensions\Query\Mysql\Date;
use phpDocumentor\Reflection\Types\Resource_;
use Symfony\Component\HttpFoundation\File\File;

class ReportService
{
    private EntityManagerInterface $em;

    private EntityRepository $orderRepo;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->orderRepo = $em->getRepository(Order::class);
    }

    /**
     * Generates a CSV file of served orders for the past 4 weeks
     */
    public function generateCsv(): File
    {
        $endDate = new DateTime('now');
        $startDate = (new DateTime('now'))->modify('-4 weeks');

        $orders = $this->getOrders('served', $startDate, $endDate, 10000);

        $headers = ['DateIssued','DateMade', 'DateServed', 'CaseNumber', 'AppointmentType', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = [
                "DateIssued" => $order->getIssuedAt()->format('Y-m-d'),
                "DateMade" => $order->getMadeAt()->format('Y-m-d'),
                "DateServed" => $order->getServedAt()->format('Y-m-d'),
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "AppointmentType" => $order->getAppointmentType(),
                "OrderType" => $order->getType()
            ];
        }

        $today = (new DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/orders-served-$today.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return new File("/tmp/orders-served-$today.csv");
    }

    /**
     * Generates a CSV file of orders waiting to be served
     */
    public function generateOrdersNotServedCsv(): File
    {
        $startDate = new DateTime("2001-01-01 00:00:00");
        $endDate = (new DateTime('now'))->modify('+1 days');

        $orders = $this->getOrders('pending', $startDate, $endDate, 1000000);

        $headers = ['CaseNumber', 'OrderType', 'OrderNumber', 'ClientName', 'OrderMadeDate', 'OrderIssueDate', 'Status' ];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = [
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "OrderType" => $order->getType(),
                "OrderNumber" => $order->getOrderNumber(),
                "ClientName" => $order->getClient()->getClientName(),
                "OrderMadeDate" => $order->getMadeAt()->format('Y-m-d'),
                "OrderIssueDate" => $order->getIssuedAt()->format('Y-m-d'),
                "Status" => 'READY TO SERVE' ? $order->readyToServe() : 'TO DO',
            ];
        }

        $today = (new DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/all-orders-not-served-$today.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return new File("/tmp/all-orders-not-served-$today.csv");
    }

    /**
     * Generates a CSV file of all served orders
     */
    public function generateAllServedOrdersCsv(): File
    {
        $startDate = new DateTime("2001-01-01 00:00:00");
        $endDate = (new DateTime('now'))->modify('+1 days');

        $orders = $this->getOrders('served', $startDate, $endDate, 1000000);

        $headers = ['CaseNumber', 'OrderType', 'OrderNumber', 'ClientName', 'OrderServedDate'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = [
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "OrderType" => $order->getType(),
                "OrderNumber" => $order->getOrderNumber(),
                "ClientName" => $order->getClient()->getClientName(),
                "OrderMadeDate" => $order->getServedAt()->format('Y-m-d'),
            ];
        }

        $today = (new DateTime('now'))->format('Y-m-d');
        $file = fopen("/tmp/all-served-orders-$today.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return new File("/tmp/all-served-orders-$today.csv");
    }

    /**
     *  Get orders that have been served into Sirius using filters
     *
     * @return Order[]
     */
    public function getOrders(string $type, DateTime $startDate, DateTime $endDate, int $maxResults): array
    {
        $formattedEndDate = $endDate->format('Y-m-d');
        $formattedStartDate = $startDate->format('Y-m-d');

        $filters = [
            'type' => $type,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate
        ];
        return $this->orderRepo->getOrders($filters, $maxResults);
    }


    /**
     * @return false|resource
     */
    public function getCasesBeforeGoLive()
    {
        $orders =  $this->orderRepo->getOrdersBeforeGoLive();

        $headers = ['DateCreated', 'DateServed', 'CaseNumber', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            if ($order->getServedAt() === null) {
                $ordersCsv[] = ["DateCreated" => $order->getCreatedAt()->format('Y-m-d'),
                    "DateServed" => 'Null',
                    "CaseNumber" => $order->getClient()->getCaseNumber(),
                    "OrderType" => $order->getType()];
            }
        }

        $file = fopen("/tmp/cases.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return $file;
    }
}
