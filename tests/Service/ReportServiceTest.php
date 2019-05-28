<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Repository\OrderRepository;
use App\Service\ReportService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ReportServiceTest extends TestCase
{
    /**
     * @var string
     */
    private $expectedCaseRef;

    /**
     * @var DateTime
     */
    private $expectedMadeAt;

    /**
     * @var string
     */
    private $expectedIssuedAt;

    /**
     * @var string
     */
    private $expectedServedAt;

    /**
     * @var []Order
     */
    private $orders;

    public function setUp()
    {
        $this->expectedCaseRef = 'COURTREFERENCE1';
        $this->expectedMadeAt = new DateTime();
        $this->expectedIssuedAt = '2019-05-23';
        $this->expectedServedAt = '2019-05-24';

        $client = new Client(
            $this->expectedCaseRef,
            'Client Name',
            new DateTime()
        );

        $orderPf = new OrderPf($client, $this->expectedMadeAt, new DateTime($this->expectedIssuedAt));
        $orderPf->setServedAt(new DateTime($this->expectedServedAt));
        $orderPf->setAppointmentType('JOINT_AND_SEVERAL');

        $orderHw = new OrderHw($client, $this->expectedMadeAt, new DateTime($this->expectedIssuedAt));
        $orderHw->setServedAt(new DateTime($this->expectedServedAt));
        $orderHw->setAppointmentType('SOLE');

        $this->orders = [$orderPf, $orderHw];
    }

    public function testGenerateCsv()
    {
        /** @var ObjectProphecy|OrderRepository $orderRepo */
        $orderRepo = $this->prophesize(OrderRepository::class);
        $orderRepo->getOrders(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($this->orders);

        /** @var ObjectProphecy|EntityManager $em */
        $em = $this->prophesize(EntityManager::class);
        $em->getRepository(Argument::any())->shouldBeCalled()->willReturn($orderRepo->reveal());

        $sut = new ReportService($em->reveal());

        $expectedCsv = <<<CSV
DateIssued,DateServed,CaseNumber,AppointmentType,OrderType
$this->expectedIssuedAt,$this->expectedServedAt,$this->expectedCaseRef,JOINT_AND_SEVERAL,PF
$this->expectedIssuedAt,$this->expectedServedAt,$this->expectedCaseRef,SOLE,HW

CSV;

        $actualCsv = $sut->generateCsv();
        $actualCsvString = file_get_contents($actualCsv->getRealPath());

        self::assertEquals($expectedCsv, $actualCsvString);
    }

    public function testGenerateCsvWithLimit()
    {
        $expectedFilters = [
            'type' => 'served',
            'maxResults' => 1
        ];

        /** @var ObjectProphecy|OrderRepository $orderRepo */
        $orderRepo = $this->prophesize(OrderRepository::class);
        $orderRepo->getOrders($expectedFilters)->shouldBeCalled()->willReturn($this->orders);

        /** @var ObjectProphecy|EntityManager $em */
        $em = $this->prophesize(EntityManager::class);
        $em->getRepository(Argument::any())->shouldBeCalled()->willReturn($orderRepo->reveal());

        $sut = new ReportService($em->reveal());

        $sut->generateCsv(1);
    }
}
