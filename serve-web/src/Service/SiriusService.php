<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Document;
use App\Entity\Order;
use App\Service\File\Storage\StorageInterface;
use Aws\EventBridge\EventBridgeClient;
use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use Psr\Log\LoggerInterface;

class SiriusService
{
    public const SIRIUS_DATE_FORMAT = 'Y-m-d';
    public const HAS_ASSETS_ABOVE_THRESHOLD_YES_SIRIUS = 'HIGH';
    public const HAS_ASSETS_ABOVE_THRESHOLD_NO_SIRIUS = 'LOW';

    public function __construct(
        private ClientInterface $httpClient,
        private ?string $siriusApiEmail,
        private ?string $siriusApiPassword,
        private EventBridgeClient $eventBridge,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private StorageInterface $S3Storage,
        private SecretsManagerClient $secretsManagerClient,
    ) {
    }

    private ?CookieJarInterface $cookieJar = null;

    public function serveOrder(Order $order): void
    {
        $this->logger->info('Sending '.$order->getType().' Order '.$order->getId().' to Sirius');

        $payload = [];
        $apiResponse = [];
        try {
            // init cookie jar to pass session token between requests
            $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            // send DC docs to Sirius
            $documents = $order->getDocuments();
            $this->logger->info('Sending '.count($documents).' docs to Sirius S3 bucket');
            $documents = $this->sendDocuments($documents);

            $this->em->flush();

            // Begin API call to Sirius
            $apiResponse = $this->login();

            if (200 == $apiResponse->getStatusCode()) {
                // generate JSON payload of order
                $this->logger->info('Logged into sirius correctly');
                $payload = $this->generateOrderPayload($order);

                if ($payload) {
                    $order->setPayloadServed($payload);

                    // Make API call
                    $this->logger->debug('Begin API call:');

                    if ($apiResponse->hasHeader('X-XSRF-TOKEN')) {
                        $csrfToken = $apiResponse->getHeader('X-XSRF-TOKEN')[0];
                    } else {
                        $csrfToken = urldecode($this->cookieJar->getCookieByName('XSRF-TOKEN')->getValue());
                    }

                    $apiResponse = $this->sendOrderToSirius($payload, $csrfToken);

                    if ($apiResponse instanceof Psr7\Response) {
                        $order->setApiResponse((array) Psr7\Message::toString($apiResponse));
                    }

                    if (200 !== $apiResponse->getStatusCode()) {
                        $this->logger->error(Psr7\Message::toString($apiResponse));
                    }
                }
            }
        } catch (RequestException $e) {
            $this->logger->error('RequestException: Request -> '.Psr7\Message::toString($e->getRequest()));
            $order->setPayloadServed($payload);

            if ($e->hasResponse()) {
                $this->logger->error('RequestException: Reponse <- '.Psr7\Message::toString($e->getResponse()));
                $order->setApiResponse(Psr7\Message::toString($e->getResponse()));
            }
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('General Exception thrown: '.$e->getMessage());

            $order->setApiResponse($e->getTraceAsString());
            $this->em->persist($order);
            $this->em->flush();

            throw $e;
        }
        try {
            $this->logout();
        } catch (RequestException $e) {
            if (401 != $e->getCode()) {
                $this->logger->error('RequestException: Reponse <- '.Psr7\Message::toString($e->getResponse()));
                throw $e;
            }
        }
    }

    /**
     * Send documents to Sirius.
     *
     * @return Collection
     */
    private function sendDocuments(Collection $documents)
    {
        $documents = $this->S3Storage->moveDocuments($documents);

        return $documents;
    }

    /**
     * Login to Sirius.
     */
    private function login()
    {
        $params = [
            'form_params' => [
                'email' => $this->siriusApiEmail,
                'password' => $this->siriusApiPassword,
            ],
            'cookies' => $this->cookieJar,
        ];

        $this->logger->debug('Logging in to '.
            $this->httpClient->getConfig('base_uri').
            ', with params => '.json_encode($params));

        return $this->httpClient->post(
            'old-login',
            $params
        );
    }

    /**
     * Ping Sirius.
     */
    public function ping(): bool
    {
        try {
            $this->httpClient->get('health-check/service-status', ['connect_timeout' => 3.14]);

            return true;
        } catch (ClientException $e) {
            $this->logger->error('Sirius has returned the status code: '.$e->getResponse()->getStatusCode().' trying to reach '.$e->getRequest()->getUri());
        } catch (ServerException $e) {
            $this->logger->error('Sirius has returned the status code: '.$e->getResponse()->getStatusCode().' trying to reach '.$e->getRequest()->getUri());
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * Send order payload to Sirius.
     *
     * @param string $payload NOT JSON encoded. Client does this with 'json' parameter.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function sendOrderToSirius($payload, string $csrfToken)
    {
        return $this->httpClient->post(
            'api/public/v1/orders',
            [
                'json' => $payload,
                'cookies' => $this->cookieJar,
                'headers' => ['X-XSRF-TOKEN' => $csrfToken],
            ]
        );
    }

    /**
     * Logout from Sirius API.
     */
    private function logout(): Psr7\Response
    {
        return $this->httpClient->post(
            'auth/logout'
        );
    }

    /**
     * Generates JSON payload for Sirius API call.
     */
    private function generateOrderPayload(Order $order): array
    {
        $dataArray = $this->generateOrderDetails($order);
        $dataArray['client'] = $this->generateClientDetails($order->getClient());
        $dataArray['deputies'] = $this->generateDeputiesDetails($order->getDeputies());
        $dataArray['documents'] = $this->generateDocumentDetails($order->getDocuments());

        return $dataArray;
    }

    /**
     * Generates Order details for Sirius API call.
     */
    private function generateOrderDetails(Order $order): array
    {
        return array_filter([
            'courtReference' => $order->getClient()->getCaseNumber(),
            'type' => $order->getType(),
            'subType' => $order->getSubType(),
            'date' => $order->getMadeAt()->format(self::SIRIUS_DATE_FORMAT),
            'issueDate' => $order->getIssuedAt()->format(self::SIRIUS_DATE_FORMAT),
            'appointmentType' => $order->getAppointmentType(),
            'assetLevel' => $this->translateHasAssetsAboveThreshold($order->getHasAssetsAboveThreshold()),
        ]);
    }

    /**
     * Generates client details as array in preparation for Sirius API call.
     */
    private function generateClientDetails(Client $client): array
    {
        return array_filter([
            'firstName' => self::extractFirstname($client->getClientName()),
            'lastName' => self::extractLastname($client->getClientName()),
        ]);
    }

    /**
     * Generates an array of deputy arrays for API call to Sirius.
     *
     * @param ArrayCollection $deputies
     */
    private function generateDeputiesDetails(Collection $deputies): array
    {
        $deputyArray = [];
        /** @var Deputy $deputy */
        foreach ($deputies as $deputy) {
            $deputyArray[] = $this->generateDeputyArray($deputy);
        }

        return $deputyArray;
    }

    /**
     * Generates data array for a single deputy.
     */
    private function generateDeputyArray(Deputy $deputy): array
    {
        return array_filter([
            'type' => $deputy->getDeputyType(),
            'firstName' => $deputy->getForename(),
            'lastName' => $deputy->getSurname(),
            'dob' => ($deputy->getDateOfBirth() instanceof \DateTime ? $deputy->getDateOfBirth()->format(self::SIRIUS_DATE_FORMAT) : ''),
            'email' => $deputy->getEmailAddress(),
            'daytimeNumber' => $deputy->getDaytimeContactNumber(),
            'eveningNumber' => $deputy->getEveningContactNumber(),
            'mobileNumber' => $deputy->getMobileContactNumber(),
            'addressLine1' => $deputy->getAddressLine1(),
            'addressLine2' => $deputy->getAddressLine2(),
            'addressLine3' => $deputy->getAddressLine3(),
            'town' => $deputy->getAddressTown(),
            'county' => $deputy->getAddressCounty(),
            'postcode' => $deputy->getAddressPostcode(),
        ]);
    }

    /**
     * Extract first name from a full name string.
     *
     * @param string $fullName
     */
    protected static function extractFirstname($fullName): string
    {
        $name = explode(' ', $fullName, 2);

        return implode(' ', array_slice($name, 0, -1));
    }

    /**
     * Extract first name from a full name string.
     *
     * @param string $fullName
     */
    protected static function extractLastname($fullName): string
    {
        $name = explode(' ', $fullName, 2);

        return implode(' ', array_slice($name, 1));
    }

    /**
     * Generates an array of document arrays for API call to Sirius.
     *
     * @param ArrayCollection $documents
     */
    private function generateDocumentDetails(Collection $documents): array
    {
        $docsArray = [];
        /** @var Document $doc */
        foreach ($documents as $doc) {
            $docsArray[] = $this->generateDocumentArray($doc);
        }

        return array_filter($docsArray);
    }

    /**
     * Generates data array for a single document.
     */
    private function generateDocumentArray(Document $document): array
    {
        return [
            'type' => $document->getType(),
            'filename' => $document->getStorageReference(),
        ];
    }

    /**
     * Generates a court reference accepted by the Sirius API.
     */
    public static function generateCourtReference(): string
    {
        $constants = [3, 4, 7, 5, 8, 2, 4];

        $ref = '';
        $sum = 0;

        foreach ($constants as $constant) {
            $value = mt_rand(0, 9);
            $ref .= $value;
            $sum += $value * $constant;
        }

        $checkbit = (11 - ($sum % 11)) % 11;
        if (10 === $checkbit) {
            $checkbit = 'T';
        }

        return $ref.$checkbit;
    }

    private function translateHasAssetsAboveThreshold(?string $hasAssetsAboveThreshold): ?string
    {
        if (Order::HAS_ASSETS_ABOVE_THRESHOLD_NA === $hasAssetsAboveThreshold || null === $hasAssetsAboveThreshold) {
            return $hasAssetsAboveThreshold;
        }

        return Order::HAS_ASSETS_ABOVE_THRESHOLD_YES === $hasAssetsAboveThreshold ?
            self::HAS_ASSETS_ABOVE_THRESHOLD_YES_SIRIUS : self::HAS_ASSETS_ABOVE_THRESHOLD_NO_SIRIUS;
    }

    public function serveOrderViaEventBus(Order $order): void
    {
        $this->logger->info('Sending '.$order->getType().' Order '.$order->getId().' via EventBridge');

        try {
            // Step 1: Generate the event payload (same as generateOrderPayload)
            $payload = $this->generateOrderPayload($order);
            if (!$payload) {
                $this->logger->error('Payload generation failed for order ID '.$order->getId());

                return;
            }

            $order->setPayloadServed($payload);

            // Step 3: Put event
            $result = $this->eventBridge->putEvents([
                'Entries' => [
                    [
                        'Source' => 'opg.supervision.serve',
                        'DetailType' => 'court-order-submitted',
                        'Detail' => json_encode([
                            'clientId' => $order->getClient()->getId(),
                            'orderId' => $order->getId(),
                            'payload' => $payload,
                        ]),
                        'EventBusName' => 'serve-bus',
                    ],
                ],
            ]);
            $this->logger->info('Event sent to EventBridge: '.json_encode($result->toArray()));

            $order->setApiResponse($result->toArray());
            $this->em->persist($order);
            $this->em->flush();
        } catch (AwsException $e) {
            $this->logger->error('EventBridge exception: '.$e->getAwsErrorMessage());
            $order->setApiResponse($e->getAwsErrorMessage());
            $this->em->persist($order);
            $this->em->flush();
            throw $e;
        } catch (\Exception $e) {
            $this->logger->error('General Exception thrown: '.$e->getMessage());
            $order->setApiResponse($e->getMessage());
            $this->em->persist($order);
            $this->em->flush();
            throw $e;
        }
    }
}
