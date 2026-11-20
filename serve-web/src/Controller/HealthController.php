<?php

namespace App\Controller;

use App\Service\Availability\DatabaseAvailability;
use App\Service\Availability\NotifyAvailability;
use App\Service\Availability\SiriusApiAvailability;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/health-check')]
class HealthController extends AbstractController
{
    public function __construct(
        EntityManager $em,
        private readonly LoggerInterface $logger,
        private readonly string $appEnv,
    ) {
    }

    #[Route(path: '', name: 'health-check', methods: ['GET'])]
    public function containerHealthAction(): ?Response
    {
        return $this->render('Health/health-check.html.twig', [
            'status' => 'OK',
        ]);
    }

    #[Route(path: '/service', methods: ['GET'])]
    public function serviceHealthAction(DatabaseAvailability $dbAvailability): ?Response
    {
        $services = [
            $dbAvailability,
        ];

        [$healthy, $services, $errors] = $this->servicesHealth($services);

        $response = $this->render('Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->appEnv,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }
    
    #[Route(path: '/pingdom', name: 'health-check-pingdom', methods: ['GET'])]
    public function pingdomHealthAction(
        DatabaseAvailability $dbAvailability,
        NotifyAvailability $notifyAvailability,
        SiriusApiAvailability $siriusAvailability,
    ): Response {
        $services = [
            $dbAvailability,
            $siriusAvailability,
            $notifyAvailability,
        ];

        [$healthy, , , $time] = $this->servicesHealth($services);

        $response = $this->render('Health/pingdom.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    #[Route(path: '/dependencies', methods: ['GET'])]
    public function dependencyHealthAction(
        NotifyAvailability $notifyAvailability,
        SiriusApiAvailability $siriusAvailability,
    ): ?Response {
        $services = [
            $siriusAvailability,
            $notifyAvailability,
        ];

        [$healthy, $services, $errors] = $this->servicesHealth($services);

        $response = $this->render('Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->appEnv,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }

    #[Route(path: '/app-env', name: 'app-env', methods: ['GET'])]
    public function appEnv(): Response
    {
        return new Response($this->appEnv);
    }

    #[Route(path: '/version', methods: ['GET'])]
    public function versionAction(): JsonResponse
    {
        return $this->json([
            'application' => getenv('APP_VERSION'),
        ]);
    }

    /**
     * @return array [true if healthy, services array, string with errors, time in secs]
     */
    private function servicesHealth(mixed $services): array
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
                if ($service->getName() != 'Sirius') {
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
            $this->logger->warning(rtrim($logObject, ',').']}');
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}
