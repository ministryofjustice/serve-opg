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

class SiriusService
{
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

    /**
     * SiriusService constructor.
     *
     * @param ClientInterface $httpClient Used for Sirius API call
     * @param StorageInterface $S3storage
     */
    public function __construct(
        EntityManager $em,
        ClientInterface $httpClient,
        StorageInterface $S3storage
    ) {
        $this->em = $em;
        $this->httpClient = $httpClient;
        $this->S3Storage = $S3storage;
    }

    public function serveOrder(Order $order)
    {
        // copy Documents to Sirius S3 bucket

        try {

//            $documents = $this->sendDocuments($order->getDocuments());
//
//            // persist documents with new location added
//            foreach ($documents as $document) {
//                $this->em->persist($document);
//            }
//            $this->em->flush();

            // Begin API call to Sirius
            // login to establish connectivity
            //$return = $this->login();

            // generate JSON payload of order
            $payload = $this->generateOrderPayload($order);

            if ($payload) {
                echo $payload;
                exit;
                // Make API call
                //$return = $this->httpClient->serveOrderToSirius($payload);
            }
            $return = $this->logout();

        } catch (\Exception $e) {
            throw $e;
        }
        exit;
    }

    /**
     * Send documents to Sirius
     *
     * @param Collection $documents
     * @return mixed
     * @throws \Exception
     */
    private function sendDocuments(Collection $documents)
    {
        $documents = $this->S3Storage->moveDocuments($documents);

        return $documents;
    }

    /**
     * Generates JSON payload for Sirius API call
     *
     * @param Order $order
     * @return string
     */
    private function generateOrderPayload(Order $order)
    {
        return json_encode(
            [
                $this->generateOrderDetails($order),
                'client' => $this->generateClientDetails($order->getClient()),
                'deputies' => $this->generateDeputiesDetails($order->getDeputies()),
                'documents' => $this->generateDocumentDetails($order->getDocuments())
            ]
        );
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
            "type" => $order->getType() == 'hw' ? 'HW' : 'PF',
            "subType" => $order->getSubType(),
            "date" => $order->getCreatedAt()->format('Y-m-d'),
            "issueDate" => $order->getIssuedAt()->format('Y-m-d'),
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
            "dob" => $deputy->getDateOfBirth()->format('Y-m-d'),
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
    private function generateDocumentDetails(   Collection $documents)
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
            "s3Link" => $document->getStorageReference()
        ];
    }

    /**
     * Login to Sirius
     */
    private function login()
    {
        $jar = new \GuzzleHttp\Cookie\CookieJar;

        try {
            $response = $this->httpClient->request(
                'POST',
                'https://frontend-datamigration.dev.sirius.opg.digital',
                [
                    'form_params' => [
                        'email'     => 'manager@opgtest.com',
                        'password' => 'Password1',
                        'cookies' => $jar
                    ]
                ]
            );
            echo 'RESPONSE--> ';
            var_dump($response);exit;
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            echo '--------------<br />';
            var_dump($e->getCode());

            var_dump($e->getMessage());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

    }

    private function logout()
    {
        try {
            $response = $this->httpClient->request(
                'POST',
                'https://frontend-datamigration.dev.sirius.opg.digital/auth/logout',
                [
                    'form_params' => []
                ]
            );
            echo 'RESPONSE--> ';
            var_dump($response);exit;
        } catch (RequestException $e) {
            echo Psr7\str($e->getRequest());
            echo '--------------<br />';
            var_dump($e->getCode());

            var_dump($e->getMessage());
            if ($e->hasResponse()) {
                echo Psr7\str($e->getResponse());
            }
        }

    }
}
