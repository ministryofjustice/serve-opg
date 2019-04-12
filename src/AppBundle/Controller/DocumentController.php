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
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    const SUCCESS = 1;
    const FAIL = 0;
    const ERROR = 2;

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

    private function processDocument(Order $order, Document $document, $file, $requestId) {

        $response = array(
            'response' => self::FAIL,
            'message' => '',
        );

        try {
            $fileObject = $this->fileCheckerFactory->factory($file);

            $fileObject->checkFile();
            if ($fileObject->isSafe()) {
                $document = $this->fileUploader->uploadFile(
                    $order,
                    $document,
                    $file
                );

                $fileName = $file->getClientOriginalName();
                $document->setFilename($fileName);

                $this->em->persist($document);
                $this->em->flush($document);

                $response["response"] = self::SUCCESS;
                $response["id"] = $document->getId();
                $response["message"] = 'File uploaded';
                $response["clientNameValid"] = $this->documentService->clientNameIsValidInFilename(
                    $fileName,
                    $order->getClient()->getClientName()
                );
                $response["docTypeValid"] = $this->documentService->docTypeIsValidInFilename(
                    $fileName,
                    $document->getType()
                );
            } else {
                $response["message"] = 'File could not be uploaded';
            }

        } catch (\Exception $e) {
            $errorToErrorTranslationKey = [
                InvalidFileTypeException::class => 'notSupported',
                RiskyFileException::class => 'risky',
                VirusFoundException::class => 'virusFound',
            ];

            $errorKey = isset($errorToErrorTranslationKey[get_class($e)]) ?
                $errorToErrorTranslationKey[get_class($e)] : 'generic';

            $message = $this->get('translator')->trans("document.file.errors.{$errorKey}", [
                '%techDetails%' => $this->getParameter('kernel.debug') ? $e->getMessage() : $requestId,
            ], 'validators');

            $this->get('logger')->error($e->getMessage()); //fully log exceptions

            $response["response"] = self::ERROR;
            $response["message"] = $message;
        }

        return $response;
    }

    private function removeDocument($id) {

        $response = self::FAIL;

        try {
            $this->documentService->deleteDocumentById($id);
            $response = self::SUCCESS;

        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());
        }

        return $response;
    }

    /**
     * @Route("/order/{orderId}/document/{docType}", methods={"POST"})
     */
    public function postAction(Request $request, $orderId, $docType)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $document = new Document($order, $docType);

        $uploadedFile = $request->files->get('file');

        $processedDocument = $this->processDocument($order, $document, $uploadedFile, $request->headers->get('x-request-id'));

        if($processedDocument["response"] === self::SUCCESS) {
            return new JsonResponse([
                'success' => true,
                'id' => $processedDocument["id"],
                'orderId' => $orderId,
                'readyToServe' => $order->readyToServe(),
                'docTypeValid' => $processedDocument['docTypeValid'],
                'clientNameValid' => $processedDocument['clientNameValid']
            ]);
        }

        if($processedDocument["response"] === self::FAIL || $processedDocument["response"] === self::ERROR) {
            return new JsonResponse([
                'error' => $processedDocument["message"]
            ], 422);
        }
    }

    /**
     * @Route("/order/{orderId}/document/{docType}/add", name="document-add")
     */
    public function addAction(Request $request, $orderId, $docType)
    {
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        $document = new Document($order, $docType);
        $form = $this->createForm(DocumentForm::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $document->getFile();
            $processedDocument = $this->processDocument($order, $document, $uploadedFile, $request->headers->get('x-request-id'));

            if($processedDocument["response"] === self::SUCCESS) {
                $request->getSession()->getFlashBag()->add('success', $processedDocument["message"]);
                return $this->redirectToRoute('order-summary', ['orderId' => $order->getId(), '_fragment' => 'documents']);
            }

            if($processedDocument["response"] === self::FAIL) {
                $request->getSession()->getFlashBag()->add('notification', $processedDocument["message"]);
            }

            if($processedDocument["response"] === self::ERROR) {
                $form->get('file')->addError(new FormError($processedDocument["message"]));
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
        if ($this->removeDocument($id) === self::FAIL) {
            $this->addFlash('error', 'Document could not be removed.');
        }

        return $this->redirectToRoute('order-summary', ['orderId' => $orderId, '_fragment' => 'documents']);
    }

    /**
     * @Route("/order/{orderId}/document/{id}", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $orderId, $id)
    {
        $documentRemoved = $this->removeDocument($id);
        $order = $this->orderService->getOrderByIdIfNotServed($orderId);

        return new JsonResponse([
            'success' => $documentRemoved,
            'readyToServe' => $order->readyToServe()
        ]);
    }
}
