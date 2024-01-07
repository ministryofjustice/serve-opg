<?php

namespace App\Service\File\Checker;

use App\Service\File\Checker\Exception\RiskyFileException;
use App\Service\File\Checker\Exception\VirusFoundException;
use App\Service\File\Types\Pdf;
use App\Service\File\Types\UploadableFileInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzlePsr7Response;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamAVChecker implements FileCheckerInterface
{
    private ClientInterface $client;

    private LoggerInterface $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * ClamAVChecker constructor.
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param array           $options
     */
    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        /** @var GuzzleHttp\Client client */
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     *
     * Checks file for viruses using ClamAv
     *
     * @param UploadableFileInterface $file
     *
     * @throws RuntimeException in case the result is not PASS
     */
    public function checkFile(UploadableFileInterface $file): UploadableFileInterface
    {
        return $file;
        /**** TO DO 2024 *****/
        /**** This looks to be work in progress 2018 *****/
        /**** Made the return type correct and commented out pending work *****/
//        // POST body to clamAV
//        $response = $this->getScanResults($file);
//
//        $file->setScanResult($response);
//
//        $isResultPass = strtoupper(trim($response['file_scanner_result'])) === 'PASS';
//
//        // log results
//        $level = $isResultPass ? Logger::INFO : Logger::ERROR;
//        $this->log($level, 'File scan result', $file->getUploadedFile(), $response);
//
//        if ($file instanceof Pdf && !$isResultPass) { // @shaun STILL NEEDED ? wouldn't this case go in the next "switch"
//            throw new RiskyFileException('PDF file scan failed');
//        }
//
//        if ($isResultPass) {
//            return $file;
//        }
//
//        switch (strtoupper(trim($response['file_scanner_code']))) {
//            case 'AV_FAIL':
//                throw new VirusFoundException();
//            case 'PDF_INVALID_FILE':
//            case 'PDF_BAD_KEYWORD':
//                throw new RiskyFileException();
//        }
//
//        throw new RuntimeException('Files scanner FAIL. Unrecognised code. Full response: ' . print_r($response));
    }

    /**
     * POSTS the file body to file scanner, and continually polls until result is returned.
     *
     * @param  UploadableFileInterface $uploadedFile
     * @return array
     */
    private function getScanResults(UploadableFileInterface $file)
    {
        // avoid contacting ClamAV for files with already-known asnwer
        if ($cachedResponse = ClamAVMocks::getCachedResponse($file)) {
            return $cachedResponse;
        }

        try {
            $result = $this->makeScannerRequest($file);

            $maxRetries = 90;
            $count = 0;
            $statusResponse = [];

            //TODO use $statusResponse['celery_task_state'] == 'SUCCESS' to verify
            while ((!array_key_exists('file_scanner_result', $statusResponse)) && ($count < $maxRetries)) {
                $statusResponse = $this->makeStatusRequest($result['location']);

                if ($statusResponse === false) {
                    $this->log(Logger::CRITICAL, 'Scanner response could not be decoded');
                    throw new \RuntimeException('Unable to contact file scanner');
                }

                sleep(1);

                $count++;
            }

            if (!array_key_exists('file_scanner_result', $statusResponse)) {
                $this->log(Logger::ERROR, 'Maximum attempts at contacting clamAV for status. Unable to retrieve complete scan result ' . $statusResponse);
            }

            return $statusResponse;
        } catch (\Exception $e) {
            $this->log(Logger::CRITICAL, 'Scanner exception: ' . $e->getCode() . ' - ' . $e->getMessage());

            throw new \RuntimeException($e);
        }
    }

    /**
     * Send file to File Scanner
     *
     * @param UploadableFileInterface $file
     *
     * @return array
     */
    private function makeScannerRequest(UploadableFileInterface $file)
    {
        $fullFilePath = $file->getUploadedFile()->getPathName();

        $response = $this->client->request('POST', $file->getScannerEndpoint(), [
            'multipart' => [
               [
                   'name'=> 'file',
                   'contents' => fopen($fullFilePath, 'r'),
               ]
            ]
        ]);

        if (!$response instanceof GuzzlePsr7Response) {
            throw new \RuntimeException('ClamAV not available');
        }
        $result = json_decode($response->getBody()->getContents(), true);

        return $result;
    }

    /**
     * Query status of file scan using location returned by AV scanner
     * @param string $location
     *
     * @return array
     */
    private function makeStatusRequest($location)
    {
        $this->log(Logger::DEBUG, 'Quering scan status for location: ' . $location);

        $response = $this->client->get($location);
        $result = json_decode($response->getBody()->getContents(), true);

        $this->log(Logger::DEBUG, 'Scan status result for location: ' . $location . ': ');

        return $result;
    }

    /**
     * @param $level
     * @param $message
     * @param UploadedFile|null $file
     * @param array|null        $response
     */
    private function log($level, $message, UploadedFile $file = null, array $response = null): void
    {
        $extra = ['service' => 'clam_av_checker'];

        if ($file) {
            $extra['fileName']  = $file->getClientOriginalName();
        }

        if ($response) {
            $extra += [
            'file_scanner_code' => $response['file_scanner_code'],
            'file_scanner_result' => $response['file_scanner_result'], //could be omitted
            'file_scanner_message' => $response['file_scanner_message']
            ];
        }

        $this->logger->log($level, $message, ['extra' => $extra]);
    }
}
