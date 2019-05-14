<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->redirectToRoute('case-list');
    }

    /**
     * @Route("/design", name="design")
     */
    public function designAction()
    {
        // design stuff
        return $this->render('AppBundle:Index:design.html.twig', [
        ]);
    }

    /**
     * keep session alive. Called from session timeout dialog.
     *
     * @Route("session-keep-alive", name="session-keep-alive")
     * @Method({"GET"})
     */
    public function sessionKeepAliveAction(Request $request)
    {
        $request->getSession()->set('refreshedAt', time());

        return new Response('session refreshed successfully');
    }
}
