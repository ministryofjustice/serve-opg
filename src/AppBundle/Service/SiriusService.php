<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Deputy;
use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use Application\Factory\GuzzleClient;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\ResultInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use AppBundle\Service\File\Storage\StorageInterface;
use Psr\Log\LoggerInterface;

class SiriusService
{
    const SIRIUS_DATE_FORMAT = 'd/m/Y';
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SiriusClient
     */
    private $httpClient;

    /**
     * @var StorageInterface
     */
    private $S3Storage;

    private $cookieJar;

    /**
     * SiriusService constructor.
     *
     * @param ClientInterface $httpClient Used for Sirius API call
     * @param StorageInterface $S3storage
     */
    public function __construct(
        EntityManager $em,
        ClientInterface $httpClient,
        StorageInterface $S3storage,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->S3Storage = $S3storage;
        $this->logger = $logger;
    }

    public function serveOrder(Order $order)
    {
        return;
        $this->logger->info('Sending ' . $order->getType() . ' Order ' . $order->getId() . ' to Sirius');

        try {
            // init cookie jar to pass session token between requests
            $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();

            // send DC docs to Sirius
            $documents = $order->getDocuments();
            $this->logger->info('Sending ' . count($documents) . ' docs to Sirius S3 bucket');
            $documents = $this->sendDocuments($documents);

            // persist documents with new location added
            foreach ($documents as $document) {
                $this->em->persist($document);
            }
            $this->em->flush();

            // Begin API call to Sirius
            $loginResponse = $this->login();

            $this->logger->debug('Sirius login response' . print_r($loginResponse));
            if ($loginResponse->getStatusCode() == 200) {
                // generate JSON payload of order
                $payload = $this->generateOrderPayload($order);

                if ($payload) {
                    $order->setPayloadServed($payload);

                    // Make API call
                    $this->logger->info('Begin API call:');

    //                    $apiResponse = $this->sendOrderToSirius($payload);
    //                    $order->setApiResponse(json_encode($apiResponse->toArray()));

                    $this->logger->debug('Sirius API response' . print_r($apiResponse));
                }
           }

            $this->logout();

        } catch (RequestException $e) {
            $this->logger->error('RequestException: Request -> ' . print_r(Psr7\str($e->getRequest())));
            if ($e->hasResponse()) {
                $this->logger->error('RequestException: Reponse <- ' . print_r(Psr7\str($e->getResponse())));
            }
        } catch (\Exception $e) {
            $this->logger->error('General Exception thrown: ' . print_r($e->getMessage()));
            throw $e;
        }
    }

    /**
     * Send documents to Sirius
     *
     * @param Collection $documents
     * @return Collection
     */
    private function sendDocuments(Collection $documents)
    {
        $documents = $this->S3Storage->moveDocuments($documents);

        return $documents;
    }

    /**
     * Login to Sirius
     */
    private function login()
    {
        $params = [
            'form_params' => [
                'email'    => getenv('SIRIUS_PUBLIC_API_EMAIL'),
                'password' => getenv('SIRIUS_PUBLIC_API_PASSWORD'),
            ],
            'cookies' => $this->cookieJar
        ];

        $this->logger->debug('Attempting to login to ' .
            $this->httpClient->getConfig('base_uri') .
            ', with ' . print_r($params));
        return $this->httpClient->post(
            'auth/login',
            $params
        );
    }


    /**
     * Send order payload to Sirius
     *
     * @param string $payload NOT JSON encoded. Client does this with 'json' parameter.
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function sendOrderToSirius($payload)
    {
        return $this->httpClient->post(
            'api/public/v1/orders',
            [
                'json' => $payload,
                'cookies' => $this->cookieJar
            ]
        );
    }

    /**
     * Logout from Sirius API
     */
    private function logout()
    {
        return $this->httpClient->post(
            'auth/logout'
        );
    }

    /**
     * Generates JSON payload for Sirius API call
     *
     * @param Order $order
     * @return string
     */
    private function generateOrderPayload(Order $order)
    {
        $dataArray = $this->generateOrderDetails($order);
        $dataArray['client'] = $this->generateClientDetails($order->getClient());
        $dataArray['deputies'] = $this->generateDeputiesDetails($order->getDeputies());
        $dataArray['documents'] = $this->generateDocumentDetails($order->getDocuments());

        return $dataArray;
    }

    /**
     * Generates Order details for Sirius API call
     *
     * @param Order $order
     * @return array
     */
    private function generateOrderDetails(Order $order)
    {
        return [
            "courtReference" => $order->getClient()->getCaseNumber(),
            "type" => $order->getType(),
            "subType" => $order->getSubType(),
            "date" => $order->getMadeAt()->format(self::SIRIUS_DATE_FORMAT),
            "issueDate" => $order->getIssuedAt()->format(self::SIRIUS_DATE_FORMAT),
            "appointmentType" => $order->getAppointmentType(),
            "assetLevel" => $order->getHasAssetsAboveThreshold() ? 'HIGH' : 'LOW',
        ];
    }

    /**
     * Generates client details as array in preparation for Sirius API call
     *
     * @param Client $client
     * @return array
     */
    private function generateClientDetails(Client $client)
    {
        return [
            "firstName" => self::extractFirstname($client->getClientName()),
            "lastName" => self::extractLastname($client->getClientName())
        ];
    }

    /**
     * Generates an array of deputy arrays for API call to Sirius
     *
     * @param ArrayCollection $deputies
     * @return array
     */
    private function generateDeputiesDetails(Collection $deputies)
    {
        $deputyArray = [];
        /** @var Deputy $deputy */
        foreach ($deputies as $deputy) {
            $deputyArray[] = $this->generateDeputyArray($deputy);
        }

        return $deputyArray;
    }

    /**
     * Generates data array for a single deputy
     *
     * @param Deputy $deputy
     * @return array
     */
    private function generateDeputyArray(Deputy $deputy)
    {
        return [
            "type" => $deputy->getDeputyType(),
            "firstName" => $deputy->getForename(),
            "lastName" => $deputy->getSurname(),
            "dob" => (!empty($deputy->getDateOfBirth()) ? $deputy->getDateOfBirth()->format(self::SIRIUS_DATE_FORMAT) : ''),
            "email" => $deputy->getEmailAddress(),
            "daytimeNumber" => $deputy->getDaytimeContactNumber(),
            "eveningNumber" => $deputy->getEveningContactNumber(),
            "mobileNumber" => $deputy->getMobileContactNumber() ,
            "addressLine1" => $deputy->getAddressLine1(),
            "addressLine2" => $deputy->getAddressLine2(),
            "addressLine3" => $deputy->getAddressLine3(),
            "town" => $deputy->getAddressTown(),
            "county" => $deputy->getAddressCounty(),
            "postcode" => $deputy->getAddressPostcode()
        ];
    }

    /**
     * Extract first name from a full name string
     *
     * @param string $fullName
     * @return mixed
     */
    protected static function extractFirstname($fullName)
    {
        $name = explode(' ', $fullName, 2);
        return implode(' ', array_slice($name, 0, -1));
    }


    /**
     * Extract first name from a full name string
     *
     * @param string $fullName
     * @return mixed
     */
    protected static function extractLastname($fullName)
    {
        $name = explode(' ', $fullName, 2);
        return implode(' ', array_slice($name, 1));
    }

    /**
     * Generates an array of document arrays for API call to Sirius
     *
     * @param ArrayCollection $documents
     *
     * @return array
     */
    private function generateDocumentDetails(Collection $documents)
    {
        $docsArray = [];
        /** @var Document $doc */
        foreach ($documents as $doc) {
            $docsArray[] = $this->generateDocumentArray($doc);
        }

        return $docsArray;
    }

    /**
     * Generates data array for a single document
     *
     * @param Document $document
     *
     * @return array
     */
    private function generateDocumentArray(Document $document)
    {
        return [
            "type" => $document->getType(),
            "filename" => $document->getStorageReference()
        ];
    }

    /**
     * Generates a court reference accepted by the Sirius API
     *
     * @return string
     */
    public static function generateCourtReference()
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
        if ($checkbit === 10) {
            $checkbit = 'T';
        }

        return $ref . $checkbit;
    }
}
