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
            $csvToArray = new CsvToArray($fileName, true);
            $rows = $csvToArray->setExpectedColumns([
                'Case',
                'ClientName',
                'OrderType',
                'IssuedAt',
            ])->getData();

            foreach ($rows as $row) {
                $this->csvImporterService->import($row);
            }

            return $this->redirectToRoute('case-list');
        }

        return $this->render('AppBundle:Csv:upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/order/{orderId}/document/{id}/remove", name="document-remove")
     */
    public function removeAction(Request $request, $orderId, $id)
    {
        try {
            $this->documentService->deleteDocumentById($id);
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
            $this->addFlash('error', 'Document could not be removed.');
        }

        return $this->redirectToRoute('order-summary', ['orderId' => $orderId]);
    }


}
