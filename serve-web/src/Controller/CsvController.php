<?php

namespace App\Controller;

use App\Form\CsvUploadForm;
use App\Service\SpreadsheetImporterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CsvController extends AbstractController
{
    private SpreadsheetImporterService $spreadsheetImporterService;

    /**
     * CsvController constructor.
     * @param SpreadsheetImporterService $spreadsheetImporterService
     */
    public function __construct(SpreadsheetImporterService $spreadsheetImporterService)
    {
        $this->spreadsheetImporterService = $spreadsheetImporterService;
    }

    #[Route(path: '/upload-csv', name: 'upload-csv')]
    public function upload(Request $request)
    {
        $form = $this->createForm(CsvUploadForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('csv_upload_form')['file'];
            $added = $this->spreadsheetImporterService->importFile($file);
            $request->getSession()->getFlashBag()->add('notification', "Processed $added orders");

            return $this->redirectToRoute('case-list');
        }

        return $this->render('Csv/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
