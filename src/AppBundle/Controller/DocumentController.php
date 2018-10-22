<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Form\DocumentForm;
use AppBundle\Service\DocumentService;
use AppBundle\Service\File\Checker\Exception\InvalidFileTypeException;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\FileCheckerFactory;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Storage\StorageInterface;
use AppBundle\Service\File\Types\Pdf;
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var DocumentService
     */
    private $documentService;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var FileCheckerFactory
     */
    private $fileCheckerFactory;

    /**
     * DocumentController constructor.
     * @param EntityManager $em
     * @param OrderService $orderService
     * @param DocumentService $documentService
     * @param FileUploader $fileUploader
     * @param FileCheckerFactory $fileCheckerFactory
     */
    public function __construct(EntityManager $em, OrderService $orderService, DocumentService $documentService, FileUploader $fileUploader, FileCheckerFactory $fileCheckerFactory)
    {
        $this->em = $em;
        $this->orderService = $orderService;
        $this->documentService = $documentService;
        $this->fileUploader = $fileUploader;
        $this->fileCheckerFactory = $fileCheckerFactory;
    }

    /**
     * @Route("/order/{orderId}/document/{docType}/add", name="document-add")
     */
    public function addAction(Request $request, $orderId, $docType)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $document = new Document($order, $docType);
        $form = $this->createForm(DocumentForm::class, $document);

        //TODO implement redirect with JS and import error message
        /*if ($request->get('error') == 'tooBig') {
            $message = $this->get('translator')->trans('document.file.errors.maxSizeMessage', [], 'validators');
            $form->get('file')->addError(new FormError($message));
        }*/
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $document->getFile();

            try {
                $fileObject = $this->fileCheckerFactory->factory($uploadedFile);

                $fileObject->checkFile();
                if ($fileObject->isSafe()) {
                    $document = $this->fileUploader->uploadFile(
                        $order,
                        $document,
                        $uploadedFile
                    );
                   $request->getSession()->getFlashBag()->add('success', 'File uploaded');

                    $fileName = $request->files->get('document_form')['file']->getClientOriginalName();
                    $document->setFilename($fileName);

                    $this->em->persist($document);
                    $this->em->flush($document);
                } else {
                   $request->getSession()->getFlashBag()->add('notification', 'File could not be uploaded');
                }

                return $this->redirectToRoute('order-summary', ['orderId' => $order->getId(), '_fragment' => 'documents']);
            } catch (\Exception $e) {
                $errorToErrorTranslationKey = [
                    InvalidFileTypeException::class => 'notSupported',
                    RiskyFileException::class => 'risky',
                    VirusFoundException::class => 'virusFound',
                ];

                $errorKey = isset($errorToErrorTranslationKey[get_class($e)]) ?
                    $errorToErrorTranslationKey[get_class($e)] : 'generic';

                $message = $this->get('translator')->trans("document.file.errors.{$errorKey}", [
                    '%techDetails%' => $this->getParameter('kernel.debug') ? $e->getMessage() : $request->headers->get('x-request-id'),
                ], 'validators');

                $request->getSession()->getFlashBag()->add('notification', 'File could not be uploaded');

                $form->get('file')->addError(new FormError($message));
                $this->get('logger')->error($e->getMessage()); //fully log exceptions
            }
        }

        return $this->render('AppBundle:Document:add.html.twig', [
            'order' => $order,
            'docType' => $docType,
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

        return $this->redirectToRoute('order-summary', ['orderId' => $orderId, '_fragment' => 'documents']);
    }
}
