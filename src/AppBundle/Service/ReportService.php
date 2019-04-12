<?php


namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;

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
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->orderRepo = $em->getRepository(Order::class);
    }

    public function generateCsv()
    {
        $orders = $this->getOrders();

        $headers = ['DateServed', 'CaseNumber', 'AppointmentType', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = ["DateServed" => $order->getServedAt()->format('Y-m-d'),
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "AppointmentType" => $order->getAppointmentType(),
                "OrderType" => $order->getType()];
        }

        $file = fopen("/tmp/orders.csv","w");

        fputcsv($file, $headers);

        foreach($ordersCsv as $line) {
            fputcsv($file, $line);
        }

        fclose($file);

        return $file;
    }

    /**
     *  Get orders that have been served into Sirius
     *
     * @return \AppBundle\Entity\Order[]
     */
    public function getOrders()
    {
        $filters = [
            'type' => 'served'
        ];
        return $this->orderRepo->getOrders($filters, 1000);
    }

    public function getCasesBeforeGoLive() {

        $orders =  $this->orderRepo->getOrdersBeforeGoLive(1000);

        $headers = ['DateServed', 'CaseNumber', 'OrderType'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = ["DateServed" => $order->getServedAt(),
                "CaseNumber" => $order->getClient()->getCaseNumber(),
                "OrderType" => $order->getType()];
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
