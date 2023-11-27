<?php


namespace App\Controller;

use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/report')]
class ReportController extends AbstractController
{
    /**
     * @var ReportService
     */
    private $reportService;

    /**
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[Route(path: '', name: 'report')]
    public function report() {
        return $this->render('Report/report.html.twig');
    }

    #[Route(path: '/download', name: 'download-report')]
    public function downloadReport() {

        $csv = $this->reportService->generateCsv();

        return $this->file($csv);
    }

    #[Route(path: '/download-orders-not-served', name: 'download-orders-not-served')]
    public function downloadOrdersNotServed() {

        $csv = $this->reportService->generateOrdersNotServedCsv();

        return $this->file($csv);
    }

    #[Route(path: '/download-served-orders', name: 'download-served-orders')]
    public function downloadServedOrders() {

        $csv = $this->reportService->generateAllServedOrdersCsv();

        return $this->file($csv);
    }

    #[Route(path: '/cases', name: 'cases')]
    public function cases() {
        return $this->render('Report/case-report.html.twig');
    }

    #[Route(path: '/download-cases', name: 'download-report-cases')]
    public function downloadCasesReport() {

        $this->reportService->getCasesBeforeGoLive();

        $csv = new File('/tmp/cases.csv');

        return $this->file($csv);
    }
}
