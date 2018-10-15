<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\SiriusService;
use Aws\SecretsManager\SecretsManagerClient;

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
     * ManageController constructor.
     * @param EntityManager $em
     * @param SiriusService $siriusService
     * @param SecretsManagerClient $secretsManagerClient
     */
    public function __construct(EntityManager $em, SiriusService $siriusService, SecretsManagerClient $secretsManagerClient)
    {
        $this->em = $em;
        $this->siriusService = $siriusService;
        $this->secretsManagerClient = $secretsManagerClient;
    }

    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $sm = $this->secretsManagerClient->describeSecret([
            "SecretId" => getenv('SIRIUS_PUBLIC_API_EMAIL')
        ])["@metadata"]['statusCode'];
        $sirius = $this->siriusService->ping();
        return $this->json([
            'sirius' => $sirius,
            'sm' => $sm
        ]);
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
