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

        $headers = ['DateServed', 'CaseNumber', 'Type'];
        $ordersCsv = [];

        foreach ($orders as $order) {
            $ordersCsv[] = ["Date Served" => $order->getServedAt()->format('d-m-Y'),
                "Case Number" => $order->getClient()->getCaseNumber(),
                "Type" => $order->getAppointmentType()];
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
}
