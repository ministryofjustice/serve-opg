<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Client;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Service\ClientService;
use App\Service\SpreadsheetImporterService;
use App\Service\OrderService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Prophecy\PhpUnit\ProphecyTrait;

class SpreadsheetImporterServiceTest extends KernelTestCase
{
    use ProphecyTrait;
    private string $projectDir;

    public function setUp(): void
    {
        $this->projectDir = self::bootKernel()->getProjectDir();
    }

    public function test_csv_importFile()
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

        $sut = new SpreadsheetImporterService(
            $clientService->reveal(),
            $orderService->reveal(),
            $em->reveal(),
            $logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }

    public function test_xlsx_importFile()
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
            new DateTime()
        );

        $row2Client = new Client(
            '93559429',
            'Amber Heard',
            new DateTime()
        );

        $clientService->upsert('93559428', 'Johnny Depp')->shouldBeCalled()->willReturn($row1Client);
        $clientService->upsert('93559429', 'Amber Heard')->shouldBeCalled()->willReturn($row2Client);

        $orderService->upsert(
            $row1Client,
            OrderPf::class,
            new DateTime('31-May-2022'),
            new DateTime('29-May-2022'),
            1
        )->shouldBeCalled();

        $orderService->upsert(
            $row2Client,
            OrderHw::class,
            new DateTime('30-May-2022'),
            new DateTime('28-May-2022'),
            2
        )->shouldBeCalled();

        $sut = new SpreadsheetImporterService(
            $clientService->reveal(),
            $orderService->reveal(),
            $em->reveal(),
            $logger->reveal()
        );

        $sut->importFile($uploadedFile);
    }
}
