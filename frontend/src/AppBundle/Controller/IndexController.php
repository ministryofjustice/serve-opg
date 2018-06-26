<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        ob_start();
        phpinfo();
        $debug = ob_get_clean();

        // deputy homepage with links to register and login
        return $this->render('AppBundle:Index:index.html.twig', [
            'debug' => $debug
        ]);
    }

}
