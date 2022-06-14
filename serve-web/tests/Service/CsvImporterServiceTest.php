<?php

declare(strict_types=1);

namespace tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\ClientService;
use App\Service\CsvImporterService;
use App\Service\OrderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\TestCase;

class CsvImporterServiceTest extends TestCase
{
    use ProphecyTrait;
    
    /** @test */
    public function importFile()
    {
        $csvFilePath = __DIR__ . '/../TestData/cases.csv';

        $clientService = $this->prophesize(ClientService::class);
        $orderService = $this->prophesize(OrderService::class);
        $em = $this->prophesize(EntityManagerInterface::class);

        $row1Client = new Client(
            '93559316',
            'Joni Mitchell',
            new DateTime()
        );

        $row2Client = new Client(
            '93559317',
            'Lorely Rodriguez',
            new DateTime()
        );

        $clientService->upsert('93559316', 'Joni Mitchell')->shouldBeCalled()->willReturn($row1Client);
        $clientService->upsert('93559317', 'Lorely Rodriguez')->shouldBeCalled()->willReturn($row2Client);

        $orderService->upsert(
            $row1Client,
            OrderPf::class,
            new DateTime('1-Aug-2018'),
            new DateTime('15-Aug-2018'),
            1
        )->shouldBeCalled();

        $orderService->upsert(
            $row2Client,
            OrderHw::class,
            new DateTime('2-Aug-2018'),
            new DateTime('17-Aug-2018'),
            2
        )->shouldBeCalled();

        $sut = new CsvImporterService($clientService->reveal(), $orderService->reveal(), $em->reveal());
        $sut->importFile($csvFilePath);
    }
}
