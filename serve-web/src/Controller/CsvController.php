<?php

namespace App\Controller;

use App\Form\CsvUploadForm;
use App\Service\ClientService;
use App\Service\OrderService;
use App\Service\SpreadsheetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CsvController extends AbstractController
{
    public function __construct(
        private readonly SpreadsheetService $spreadsheetService,
        private readonly OrderService $orderService,
        private readonly ClientService $clientService,
    ) {}

    #[Route(path: '/upload-csv', name: 'upload-csv')]
    public function uploadAction(Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(CsvUploadForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('csv_upload_form')['file'];
            $added = $this->spreadsheetService->importFile($file);
            $request->getSession()->getFlashBag()->add('notification', "Processed $added orders");

            return $this->redirectToRoute('case-list');
        }

        return $this->render('Csv/upload-cases.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/multiple-case-removal', name: 'case-removal')]
    public function caseDeleteUploadAction(Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(CsvUploadForm::class);
        $form->handleRequest($request);

        $displayItems = [
            'form' => $form->createView()
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('csv_upload_form')['file'];
            $processedCases = $this->spreadsheetService->processDeletionsFile($file);

            $displayResults = [
                'ordersRemoved' => [],
                'skippedCases' => $processedCases['skippedCases'],
            ];
            foreach ($processedCases['removeCases'] as $processedResults) {
                $ordersRemoved = 0;
                $this->clientService->deletionByClientId($processedResults['clientId']);

                foreach ($processedResults['orders'] as $orderId) {
                    $this->orderService->deletionByOrderId($orderId);
                    ++$ordersRemoved;
                }

                $displayResults['ordersRemoved'][] = [
                    'caseNumber' => $processedResults['caseNumber'],
                    'ordersRemovedCount' => $ordersRemoved
                ];
            }
            $displayItems['processedResults'] = $displayResults;
        }

        return $this->render('Csv/multiple-case-removal.html.twig', $displayItems);
    }
}
