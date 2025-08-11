<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\ClientService;
use App\Service\OrderService;
use App\Service\SpreadsheetService;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpreadsheetImporterServiceTest extends KernelTestCase
{
    use ProphecyTrait;
    private string $projectDir;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();
    }

    public function testCsvImportFile()
    {
        $csvFilePath = 'tests/TestData/cases.csv';

        $filePath = sprintf('%s/%s', $this->projectDir, $csvFilePath);
        $uploadedFile = new UploadedFile($filePath, 'cases.csv', 'text/csv');

        $clientService = $this->prophesize(ClientService::class);
        $orderService = $this->prophesize(OrderService::class);
        $em = $this->prophesize(EntityManagerInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $row1Client = new Client(
            '93559316',
            'Joni Mitchell',
            new \DateTime()
        );

        $row2Client = new Client(
            '93559317',
            'Lorely Rodriguez',
            new \DateTime()
        );

        $clientService->upsert('93559316', 'Joni Mitchell')->shouldBeCalled()->willReturn($row1Client);
        $clientService->upsert('93559317', 'Lorely Rodriguez')->shouldBeCalled()->willReturn($row2Client);
        $clientService->upsert('93559317', 'Lorely Rodriguez')->shouldBeCalled()->willReturn($row2Client);

        $row1Order = new OrderPf(
            $row1Client,
            new \DateTime('1-Aug-2018'),
            new \DateTime('15-Aug-2018'),
            '1'
        );
        $orderService->upsert(
            $row1Client,
            OrderPf::class,
            new \DateTime('1-Aug-2018'),
            new \DateTime('15-Aug-2018'),
            '1'
        )->shouldBeCalled()
        ->willReturn($row1Order);

        $row2Order = new OrderHw(
            $row2Client,
            new \DateTime('2-Aug-2018'),
            new \DateTime('17-Aug-2018'),
            '2'
        );
        $orderService->upsert(
            $row2Client,
            OrderHw::class,
            new \DateTime('2-Aug-2018'),
            new \DateTime('17-Aug-2018'),
            '2'
        )->shouldBeCalled()
        ->willReturn($row2Order);

        $row3Order = new OrderPf(
            $row2Client,
            new \DateTime('3-Aug-2018'),
            new \DateTime('18-Aug-2018'),
            '3'
        );
        $orderService->upsert(
            $row2Client,
            OrderPf::class,
            new \DateTime('3-Aug-2018'),
            new \DateTime('18-Aug-2018'),
            '3'
        )->shouldBeCalled()
            ->willReturn($row3Order);

        $sut = new SpreadsheetService(
            $clientService->reveal(),
            $orderService->reveal(),
            $em->reveal(),
            $logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }

    public function testXlsxImportFile()
    {
        $csvFilePath = 'tests/TestData/cases.xlsx';

        $filePath = sprintf('%s/%s', $this->projectDir, $csvFilePath);
        $uploadedFile = new UploadedFile(
            $filePath,
            'cases.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $clientService = $this->prophesize(ClientService::class);
        $orderService = $this->prophesize(OrderService::class);
        $em = $this->prophesize(EntityManagerInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $row1Client = new Client(
            '93559428',
            'Johnny Depp',
            new \DateTime()
        );

        $row2Client = new Client(
            '93559429',
            'Amber Heard',
            new \DateTime()
        );

        $clientService->upsert('93559428', 'Johnny Depp')->shouldBeCalled()->willReturn($row1Client);
        $clientService->upsert('93559429', 'Amber Heard')->shouldBeCalled()->willReturn($row2Client);

        $row1Order = new OrderPf(
            $row1Client,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        );
        $orderService->upsert(
            $row1Client,
            OrderPf::class,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        )->shouldBeCalled()
        ->willReturn($row1Order);

        $row2Order = new OrderPf(
            $row1Client,
            new \DateTime('31-May-2022'),
            new \DateTime('29-May-2022'),
            '1'
        );
        $orderService->upsert(
            $row2Client,
            OrderHw::class,
            new \DateTime('30-May-2022'),
            new \DateTime('28-May-2022'),
            '2'
        )->shouldBeCalled()
        ->willReturn($row2Order);

        $sut = new SpreadsheetService(
            $clientService->reveal(),
            $orderService->reveal(),
            $em->reveal(),
            $logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }
}
