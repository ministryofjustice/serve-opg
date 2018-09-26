<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use Application\Factory\GuzzleClient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use AppBundle\Service\File\Storage\StorageInterface;

class SiriusService
{
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
     * @param ClientInterface $httpClient
     * @param StorageInterface $S3storage
     */
    public function __construct(
        ClientInterface $httpClient,
        StorageInterface $S3storage
    ) {
        $this->httpClient = $httpClient;
        $this->S3Storage = $S3storage;
    }

    public function serveOrder(Order $order)
    {
        // copy Documents to Sirius S3 bucket
        $this->sendDocuments($order->getDocuments());

        // generate JSON payload
        //$payload = $this->generatePayload($order);

        // Make API call
        //$return = $this->login();
        //$return = $this->httpClient->serveOrderToSirius($payload);

    }

    private function sendDocuments(Collection $documents)
    {
//        $sourceBucket = '*** Your Source Bucket Name ***';
//        $sourceKeyname = '*** Your Source Object Key ***';
//        $targetBucket = '*** Your Target Bucket Name ***';
//
//
//// Copy an object.
//        $s3->copyObject([
//            'Bucket'     => $this->siriusS3Storage->get,
//            'Key'        => "{$sourceKeyname}-copy",
//            'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
//        ]);
//
//// Perform a batch of CopyObject operations.
//        $batch = array();
//        for ($i = 1; $i <= 3; $i++) {
//            $batch[] = $s3->getCommand('CopyObject', [
//                'Bucket'     => $targetBucket,
//                'Key'        => "{targetKeyname}-{$i}",
//                'CopySource' => "{$sourceBucket}/{$sourceKeyname}",
//            ]);
//        }
//        try {
//            $results = CommandPool::batch($s3, $batch);
//            foreach($results as $result) {
//                if ($result instanceof ResultInterface) {
//                    // Result handling here
//                }
//                if ($result instanceof AwsException) {
//                    // AwsException handling here
//                }
//            }
//        } catch (\Exception $e) {
//            // General error handling here
//        }
        var_dump($documents);
        exit;
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
