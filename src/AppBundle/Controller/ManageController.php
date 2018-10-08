<?php

namespace AppBundle\Controller;

use AppBundle\Service\Security\LoginAttempts\Checker;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/manage")
 */
class ManageController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Checker
     */
    private $loginChecker;

    /**
     * ManageController constructor.
     * @param EntityManager $em
     * @param Checker $loginChecker
     */
    public function __construct(EntityManager $em, Checker $loginChecker)
    {
        $this->em = $em;
        $this->loginChecker = $loginChecker;
    }


    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $errors = [];

        $this->loginChecker->registerUserLoginFailure('test', time());
        $this->loginChecker->isUserLocked('elvis@gmail');


        if ($errors) {
            return new Response('<h1>ERRORS</h1><li>' . implode('</li><li>', $errors) . '</li></ul> ', 500);
        }

        return new Response('OK', 200);
    }

    /**
     * @Route("/version")
     * @Method({"GET"})
     * @Template
     */
    public function versionAction()
    {
        return $this->json(['version' => getenv("APP_VERSION")]);
    }

    /**
     * @Route("/availability/pingdom")
     * @Method({"GET"})
     */
    public function healthCheckXmlAction()
    {
        $healthy = true;
        $time = 100;

        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/elb", name="manage-elb")
     * @Method({"GET"})
     * @Template()
     */
    public function elbAction()
    {
        return ['status' => 'OK'];
    }

}
