<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index()
    {
        return $this->redirectToRoute('case-list');
    }

    #[Route(path: '/design', name: 'design')]
    public function design()
    {
        // design stuff
        return $this->render('Index/design.html.twig', [
        ]);
    }

    /**
     * keep session alive. Called from session timeout dialog.
     */
    #[Route(path: 'session-keep-alive', name: 'session-keep-alive', methods: ['GET'])]
    public function sessionKeepAlive(Request $request)
    {
        $request->getSession()->set('refreshedAt', time());

        return new Response('session refreshed successfully');
    }
}
