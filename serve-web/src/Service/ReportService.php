<?php


namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
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

    public function generateCsv()
    {
        $orders = $this->getOrders();

        $headers = ['DateIssued','DateServed', 'CaseNumber', 'AppointmentType', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = [
                "DateIssued" => $order->getIssuedAt()->format('Y-m-d'),
                "DateServed" => $order->getServedAt()->format('Y-m-d'),
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "AppointmentType" => $order->getAppointmentType(),
                "OrderType" => $order->getType()
            ];
        }

        $file = fopen("/tmp/orders.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return new File('/tmp/orders.csv');
    }

    /**
     *  Get orders that have been served into Sirius
     *
     * @return \App\Entity\Order[]
     */
    public function getOrders()
    {
        $filters = [
            'type' => 'served'
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
