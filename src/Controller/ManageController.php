<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Service\SiriusService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
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
     * @var string
     */
    private $appEnv;

    /**
     * ManageController constructor.
     * @param EntityManager $em
     * @param SiriusService $siriusService
     * @param string $appEnv
     */
    public function __construct(EntityManager $em, SiriusService $siriusService, string $appEnv)
    {
        $this->em = $em;
        $this->siriusService = $siriusService;
        $this->appEnv = $appEnv;
    }

    /**
     * @Route("/availability", methods={"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $sirius = $this->siriusService->ping();
        return $this->json([
            'sirius' => $sirius
        ]);
    }

    /**
     * @Route("/version", methods={"GET"})
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
     * @Route("/availability/pingdom", methods={"GET"})
     */
    public function healthCheckXmlAction()
    {
        $healthy = true;
        $time = 100;

        $response = $this->render('Manage/health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/elb", name="manage-elb", methods={"GET"})
     * @Template()
     */
    public function elbAction()
    {
        return $this->render('Manage/elb.html.twig', [
            'status' => 'OK'
        ]);
    }

    /**
     * @Route("/app-env", name="app-env", methods={"GET"})
     */
    public function appEnv()
    {
        return new Response('appEnv is: ' . ($this->appEnv) . ' kernelEnv is: ' . $this->kernelEnv);
    }
}
