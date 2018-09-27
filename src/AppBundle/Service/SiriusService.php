<?php

namespace AppBundle\Service;

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

            $documents = $this->sendDocuments($order->getDocuments());

            foreach ($documents as $document) {
                $this->em->persist($document);
            }

            $this->em->flush();

            // generate JSON payload
            //$payload = $this->generatePayload($order);

            // Make API call
            //$return = $this->login();
            //$return = $this->httpClient->serveOrderToSirius($payload);

        } catch (\Exception $e) {
            throw $e;
        }
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

//    private function login()
//    {
//        $jar = new \GuzzleHttp\Cookie\CookieJar;
//
//        try {
//            $response = $this->httpClient->request(
//                'POST',
//                'https://localhost:8080/auth/login',
//                [
//                        'email' => 'manager@opgtest.com',
//                        'password' => 'Password1',
////                        'cookies' => $jar
//                ]
//            );
//            echo 'RESPONSE--> ';
//            var_dump($response);exit;
//        } catch (RequestException $e) {
//            echo Psr7\str($e->getRequest());
//            echo '--------------<br />';
//            var_dump($e->getCode());
//
//            var_dump($e->getMessage());
//            if ($e->hasResponse()) {
//                echo Psr7\str($e->getResponse());
//            }
//        }
//
//    }
}
