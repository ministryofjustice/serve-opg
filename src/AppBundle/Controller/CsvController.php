<?php

namespace AppBundle\Controller;

use AppBundle\Form\CsvUploadForm;
use AppBundle\Service\CsvImporterService;
use AppBundle\Service\CsvToArray;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CsvController extends Controller
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
            $request->getSession()->getFlashBag()->add('notice', "Processed $added orders");

            return $this->redirectToRoute('case-list');
        }

        return $this->render('AppBundle:Csv:upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
