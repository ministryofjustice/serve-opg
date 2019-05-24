<?php declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Repository\OrderRepository;
use App\Service\ReportService;
use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class ReportServiceTest extends TestCase
{

    public function testGenerateCsv()
    {
        $expectedCaseRef = 'COURTREFERENCE1';

        $client = new Client(
            $expectedCaseRef,
            'Client Name',
            new DateTime()
        );

        $expectedMadeAt = new DateTime();
        $expectedIssuedAt = '2019-05-23';
        $expectedServedAt = '2019-05-24';

        $orderPf = new OrderPf($client, $expectedMadeAt, new DateTime($expectedIssuedAt));
        $orderPf->setServedAt(new DateTime($expectedServedAt));
        $orderPf->setAppointmentType('JOINT_AND_SEVERAL');

        $orderHw = new OrderHw($client, $expectedMadeAt, new DateTime($expectedIssuedAt));
        $orderHw->setServedAt(new DateTime($expectedServedAt));
        $orderHw->setAppointmentType('SOLE');

        $orders = [$orderPf, $orderHw];

        /** @var ObjectProphecy|OrderRepository $orderRepo */
        $orderRepo = $this->prophesize(OrderRepository::class);
        $orderRepo->getOrders(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($orders);

        /** @var ObjectProphecy|EntityManager $em */
        $em = $this->prophesize(EntityManager::class);
        $em->getRepository(Argument::any())->shouldBeCalled()->willReturn($orderRepo->reveal());

        $sut = new ReportService($em->reveal());

        $expectedCsv = <<<CSV
DateServed,CaseNumber,AppointmentType,OrderType
$expectedServedAt,$expectedCaseRef,JOINT_AND_SEVERAL,PF
$expectedServedAt,$expectedCaseRef,SOLE,HW

CSV;

        $actualCsv = $sut->generateCsv();
        $actualCsvString = file_get_contents($actualCsv->getRealPath());

        self::assertEquals($expectedCsv, $actualCsvString);
    }
}
