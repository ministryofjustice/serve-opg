<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function indexAction(): RedirectResponse
    {
        return $this->redirectToRoute('case-list');
    }

    #[Route(path: '/design', name: 'design')]
    public function designAction(): Response
    {
        // design stuff
        return $this->render('Index/design.html.twig', [
        ]);
    }

    /**
     * keep session alive. Called from session timeout dialog.
     */
    #[Route(path: 'session-keep-alive', name: 'session-keep-alive', methods: ['GET'])]
    public function sessionKeepAliveAction(Request $request): Response
    {
        $request->getSession()->set('refreshedAt', time());

        return new Response('session refreshed successfully');
    }
}
