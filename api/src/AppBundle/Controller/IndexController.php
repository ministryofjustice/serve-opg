<?php

namespace AppBundle\Controller;

use Common\Entity\User;
use AppBundle\Service\RouteSelfDocumentor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;


class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $d = new RouteSelfDocumentor($this->get('router'));

        return new Response($d->getHtml());
    }

    /**
     * @Route("/exception")
     */
    public function exceptionAction()
    {
        throw new \RuntimeException('custom exception');
    }

}
