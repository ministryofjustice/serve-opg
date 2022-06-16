<?php


namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ReportService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var OrderRepository
     */
    private $orderRepo;


    /**
     * ReportService constructor
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->orderRepo = $em->getRepository(Order::class);
    }

    /**
     * Generates a CSV file of served orders for the past 4 weeks
     * @return File
     */
    public function generateCsv(): File
    {
        $orders = $this->getOrders();

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
     *  Get orders that have been served into Sirius for the last 4 weeks
     *
     * @return Order[]
     */
    public function getOrders()
    {
        $today = (new DateTime('now'))->format('Y-m-d');
        $minus4Weeks = (new DateTime('now'))->modify('-4 weeks')->format('Y-m-d');

        $filters = [
            'type' => 'served',
            'startDate' => $minus4Weeks,
            'endDate' => $today
        ];
        return $this->orderRepo->getOrders($filters, 10000);
    }

    public function getCasesBeforeGoLive() {

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
