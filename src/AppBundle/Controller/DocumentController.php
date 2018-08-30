<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use AppBundle\Form\DeputyForm;
use AppBundle\Form\DocumentForm;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        $doc = new Document($order, $docType);
        $form = $this->createForm(DocumentForm::class, $doc);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//                $file = $doc->getFile();

//            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $this->em->persist($doc);
            $this->em->flush($doc);

            return $this->redirectToRoute('order-summary', ['orderId' => $order->getId()]);
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
        $doc = $this->em->getRepository(Document::class)->find($id); /* @var $doc Document */
        if (!$doc) {
            throw new \RuntimeException("not found");
        }

        $this->em->remove($doc);
        $this->em->flush($doc);

        return $this->redirectToRoute('order-summary', ['orderId' => $orderId]);
    }
}
