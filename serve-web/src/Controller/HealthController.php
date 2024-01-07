<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Service\Availability\DatabaseAvailability;
use App\Service\Availability\SiriusApiAvailability;
use App\Service\Availability\NotifyAvailability;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * @Route("/health-check")
 */
class HealthController extends AbstractController
{
    private EntityManager $em;

    private string $appEnv;

    private string $symfonyDebug = '';

    /**
     * HealthController constructor.
     * @param EntityManager $em
     * @param string $appEnv
     * @param string %symfonyDebug
     */
    public function __construct(
        EntityManager $em,
        private LoggerInterface $logger,
        string $appEnv,
        string $symfonyDebug
        ) {

        $this->em = $em;
        $this->appEnv = $appEnv;
    }

    /**
     * @Route("", name="health-check", methods={"GET"})
     * @Template("Health/health-check.html.twig")
     */
    public function containerHealthAction(): array
    {
        return ['status' => 'OK'];
    }

    /**
     * @Route("/service", methods={"GET"})
     */
    public function serviceHealthAction(
        DatabaseAvailability $dbAvailability
    ): ?Response {
        $services = [
            $dbAvailability
        ];

        list($healthy, $services, $errors) = $this->servicesHealth($services);

        $response = $this->render('Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->appEnv,
            'debug' => $this->symfonyDebug,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
        }

    /**
     * @Route("/dependencies", methods={"GET"})
     */
    public function dependencyHealthAction(
        NotifyAvailability $notifyAvailability,
        SiriusApiAvailability $siriusAvailability
    ): ?Response {
        $services = [
            $siriusAvailability,
            $notifyAvailability
        ];

        list($healthy, $services, $errors) = $this->servicesHealth($services);

        $response = $this->render('Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->appEnv,
            'debug' => $this->symfonyDebug,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }

    /**
     * @Route("/app-env", name="app-env", methods={"GET"})
     */
    public function appEnv(): Response
    {
        return new Response($this->appEnv);
    }

    /**
     * @Route("/version", methods={"GET"})
     *
     * @Template
     */
    public function versionAction(): JsonResponse
    {
        return $this->json([
            'application' => getenv('APP_VERSION'),
            'web' => getenv('WEB_VERSION'),
            'infrastructure' => getenv('INFRA_VERSION'),
        ]);
    }

    /**
     * @param mixed $services
     *
     * @return array [true if healthy, services array, string with errors, time in secs]
     */
    private function servicesHealth($services): array
    {
        $start = microtime(true);

        $healthy = true;
        $logResponses = false;
        $errors = [];
        $logObject = 'Availability Warning - {[';

        foreach ($services as $service) {
            $startServiceTime = microtime(true);

            $service->ping();

            if (!$service->isHealthy()) {
                $logResponses = true;
                if ('Sirius' != $service->getName()) {
                    $healthy = false;
                }
                $errors[] = $service->getErrors();
            }
            $serviceTimeTaken = (microtime(true) - $startServiceTime);
            $logObject = $logObject.sprintf(
                '["service": "%s", "time": "%s", error: "%s"],',
                $service->getName(),
                round($serviceTimeTaken, 3),
                $service->getErrors()
            );
        }

        if ($logResponses) {
            $this->logger->warning(strval(rtrim($logObject, ',').']}'));
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}
