<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\SiriusService;

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
     * @var SiriusService 
     */
    private $siriusService;

    /**
     * ManageController constructor.
     * @param EntityManager $em
     * @param SiriusService $siriusService
     */
    public function __construct(EntityManager $em, SiriusService $siriusService)
    {
        $this->em = $em;
        $this->siriusService = $siriusService;
    }

    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $status = $this->siriusService->ping();
        return $this->json(['sirius' => $status]);
    }

    /**
     * @Route("/version")
     * @Method({"GET"})
     * @Template
     */
    public function versionAction()
    {
        return $this->json([
            'application' => getenv("APP_VERSION"),
            'web' => getenv("WEB_VERSION"),
            'infrastructure' => getenv("INFRA_VERSION")
        ]);
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
