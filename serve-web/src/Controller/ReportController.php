<?php

namespace App\Controller;

use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/report")
 */
class ReportController extends AbstractController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @Route("", name="report")
     */
    public function reportAction(): Response
    {
        return $this->render('Report/report.html.twig');
    }

    /**
     * @Route("/download", name="download-report")
     */
    public function downloadReportAction(): BinaryFileResponse
    {
        $csv = $this->reportService->generateCsv();

        return $this->file($csv);
    }

    /**
     * @Route("/download-orders-not-served", name="download-orders-not-served")
     */
    public function downloadOrdersNotServed(): BinaryFileResponse
    {
        $csv = $this->reportService->generateOrdersNotServedCsv();

        return $this->file($csv);
    }

    /**
     * @Route("/download-served-orders", name="download-served-orders")
     */
    public function downloadServedOrders(): BinaryFileResponse
    {
        $csv = $this->reportService->generateAllServedOrdersCsv();

        return $this->file($csv);
    }

    /**
     * @Route("/cases", name="cases")
     */
    public function casesAction(): Response
    {
        return $this->render('Report/case-report.html.twig');
    }

    /**
     * @Route("/download-cases", name="download-report-cases")
     */
    public function downloadCasesReportAction(): BinaryFileResponse
    {
        $this->reportService->getCasesBeforeGoLive();

        $csv = new File('/tmp/cases.csv');

        return $this->file($csv);
    }
}
