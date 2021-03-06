<?php

namespace App\Controller;

use App\Form\CsvUploadForm;
use App\Service\CsvImporterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CsvController extends AbstractController
{
    /**
     * @var CsvImporterService
     */
    private $csvImporterService;

    /**
     * CsvController constructor.
     * @param CsvImporterService $csvImporterService
     */
    public function __construct(CsvImporterService $csvImporterService)
    {
        $this->csvImporterService = $csvImporterService;
    }

    /**
     * @Route("/upload-csv", name="upload-csv")
     */
    public function uploadAction(Request $request)
    {
        $form = $this->createForm(CsvUploadForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $form->get('file')->getData();
            $added = $this->csvImporterService->importFile($fileName);
            $request->getSession()->getFlashBag()->add('notification', "Processed $added orders");

            return $this->redirectToRoute('case-list');
        }

        return $this->render('Csv/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
