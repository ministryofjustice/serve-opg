<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Form\DeputyForm;
use AppBundle\Form\DocumentForm;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use Doctrine\ORM\EntityManager;
use AppBundle\Service\File\Checker\FileCheckerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/order/{orderId}/document/{docType}/add", name="document-add")
     */
    public function addAction(Request $request, $orderId, $docType)
    {
        $order = $this->em->getRepository(Order::class)->find($orderId);
        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }

        $document = new Document($order, $docType);
        $form = $this->createForm(DocumentForm::class, $document);

//        if ($request->get('error') == 'tooBig') {
//            $message = $this->get('translator')->trans('document.file.errors.maxSizeMessage', [], 'validators');
//            $form->get('file')->addError(new FormError($message));
//        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $fileUploader = $this->container->get('file_uploader');

            /* @var $uploadedFile UploadedFile */
            $uploadedFile = $document->getFile();

            /** @var FileCheckerInterface $fileChecker */
            $fileChecker = $this->get('file_checker_factory')->factory($uploadedFile);

            try {
                $fileChecker->checkFile();

                if ($fileChecker->isSafe()) {
//                    $document = $fileUploader->uploadFile(
//                        $order,
//                        file_get_contents($uploadedFile->getPathName()),
//                        $uploadedFile->getClientOriginalName(),
//                        false
//                    );
                    $request->getSession()->getFlashBag()->add('notice', 'File uploaded');


                    $fileName = $request->files->get('document_form')['file']->getClientOriginalName();

                    $document->setFilename($fileName);

                    $this->em->persist($document);
                    $this->em->flush($document);
                } else {
                    $request->getSession()->getFlashBag()->add('notice', 'File could not be uploaded');
                }

                return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
            } catch (\Exception $e) {

                $errorToErrorTranslationKey = [
                    RiskyFileException::class => 'risky',
                    VirusFoundException::class => 'virusFound',
                ];
                $errorClass = get_class($e);
                if (isset($errorToErrorTranslationKey[$errorClass])) {
                    $errorKey = $errorToErrorTranslationKey[$errorClass];
                } else {
                    $errorKey = 'generic';
                }
                $message = $this->get('translator')->trans("document.file.errors.{$errorKey}", [
                    '%techDetails%' => $this->getParameter('kernel.debug') ? $e->getMessage() : $request->headers->get('x-request-id'),
                ], 'validators');
                $form->get('file')->addError(new FormError($message));
                $this->get('logger')->error($e->getMessage()); //fully log exceptions
            }
        }







            /***** OLD ***/
//                $file = $document->getFile(); // upload this to S3

//            $fileName = $request->files->get('document_form')['file']->getClientOriginalName();
//
//            $document->setFile($fileName);
//
//            $this->em->persist($document);
//            $this->em->flush($document);

//            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
//        }

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
        $doc = $this->em->getRepository(Document::class)->find($id); /* @var $doc Document */
        if (!$doc) {
            throw new \RuntimeException("not found");
        }

        $this->em->remove($doc);
        $this->em->flush($doc);

        return $this->redirectToRoute('order-summary', ['orderId' => $orderId]);
    }
}
