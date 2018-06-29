<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DocumentsController extends Controller
{
    /**
     * @Route("/documents", name="documents")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Documents:index.html.twig', [

        ]);
    }
}