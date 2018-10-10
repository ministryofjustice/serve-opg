<?php

namespace AppBundle\Service\File\Storage;

use Aws\CommandInterface;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Doctrine\Common\Collections\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to upload/download/delete files from S3
 *
 * Original logic
 * https://github.com/ministryofjustice/opg-av-test/blob/master/public/index.php
 */
class S3Storage implements StorageInterface
{
    /**
     * @var S3Client
     *
     * https://github.com/aws/aws-sdk-php
     * http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html
     *
     *
     * for fake s3:
     * https://github.com/jubos/fake-s3
     * https://github.com/jubos/fake-s3/wiki/Supported-Clients
     */
    private $s3Client;

    /**
     * @var string
     */
    private $localBucketName;

    /**
     * @var string
     */
    private $remoteBucketName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * S3Storage constructor.
     * @param S3ClientInterface $s3Client
     * @param $localBucketName
     * @param $remoteBucketName
     * @param LoggerInterface $logger
     */
    public function __construct(S3ClientInterface $s3Client, $localBucketName, $remoteBucketName, LoggerInterface $logger)
    {
        $this->s3Client = $s3Client;
        $this->localBucketName = $localBucketName;
        $this->remoteBucketName = $remoteBucketName;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getLocalBucketName()
    {
        return $this->localBucketName;
    }

    /**
     * @return string
     */
    public function getRemoteBucketName()
    {
        return $this->remoteBucketName;
    }

    /**
     * Gets file content
     * To download it, use
     * header('Content-Disposition: attachment; filename="' . $_GET['filename'] .'"');
     * readfile(<this method>);
     *
     * @param $key
     *
     * @throws FileNotFoundException is the file is not found
     *
     * @return string file content
     */
    public function retrieve($key)
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->localBucketName,
                'Key'    => $key
            ]);

            return $result['Body'];
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                throw new FileNotFoundException("Cannot find file with reference $key");
            }
            throw $e;
        }
    }

    /**
     * @param  string      $key
     * @return \Aws\Result
     */
    public function delete($key)
    {
        /** If no access to remove, we'll need to reimplment tagging **/

        //$this->appendTagset($key, [['Key' => 'Purge', 'Value' => 1]]);

        return $this->s3Client->deleteObject([
            'Bucket' => $this->localBucketName,
            'Key'    => $key
        ]);
    }

    /**
     * @param $key
     * @param $body
     * @return \Aws\Result
     */
    public function store($key, $body)
    {
        return $this->s3Client->putObject([
            'Bucket'   => $this->localBucketName,
            'Key'      => $key,
            'Body'     => $body,
            'ServerSideEncryption' => 'AES256',
            'Metadata' => []
        ]);
    }

    /**
     * Move S3 Objects To new bucket
     * @param Collection $documents
     * @return \Generator
     */
    public function moveDocuments(Collection $documents)
    {
        // set up variables used in closures
        $s3Client = $this->s3Client;
        $logger = $this->logger;
        $documentsIterator = $documents->getIterator();

        // Create a generator that converts the source objects into
        // Aws\CommandInterface objects. This generator accepts the iterator that
        // yields files and the name of the bucket to upload the files to.
        $commandGenerator = function (\Iterator $documentsIterator, $targetBucket) use ($s3Client) {
            foreach ($documentsIterator as $document) {
                // Yield a command that will be executed by the pool
                yield $s3Client->getCommand('CopyObject', [
                    'Bucket'     => $targetBucket,
                    'Key'        => $document->getStorageReference(),
                    'CopySource' => urlencode($this->getLocalBucketName() . '/' . $document->getStorageReference())
                ]);
            }
        };

        // Create the generator using the collection iterator
        $commands = $commandGenerator($documentsIterator, $this->getRemoteBucketName());
        
        // Create a pool and provide an optional array of configuration
        $pool = new CommandPool($s3Client, $commands, [
            // Only send 5 files at a time (this is set to 25 by default)
            'concurrency' => 5,
            'preserve_iterator_keys' => true,
            // Invoke this function before executing each command
            'before' => function (CommandInterface $cmd, $iterKey) use ($logger) {
                $logger->debug("About to send {$iterKey}: " . print_r($cmd->toArray(), true));
            },
            // Invoke this function for each successful transfer
            'fulfilled' => function (
                ResultInterface $result,
                $iterKey,
                PromiseInterface $aggregatePromise
            )  use ($logger, $documentsIterator) {

                // update current document being processed with new location
                $documentsIterator[$iterKey]->setRemoteStorageReference($result->get('@metadata')['effectiveUri']);
                $logger->debug("Completed {$iterKey}: {$result}");
            },
            // Invoke this function for each failed transfer
            'rejected' => function (
                AwsException $reason,
                $iterKey,
                PromiseInterface $aggregatePromise
            ) use ($logger, $documents) {
                $logger->error("Failed to send {$iterKey}: {$reason}\n");
            },
        ]);

        // Initiate the pool transfers
        $promise = $pool->promise();

        // Force the pool to complete synchronously
        $promise->wait();

        $promise->then(function() use ($logger) {
            $logger->info("Transfer complete");
        });

        return $documents;
    }

    /**
     * Appends new tagset to S3 Object
     *
     * @param $key
     * @param $newTagset
     * @throws \Exception
     */
    public function appendTagset($key, $newTagset)
    {
        $this->log('info', "Appending Purge tag for $key to S3");
        if (empty($key)) {
            throw new \Exception('Invalid Reference Key: ' . $key . ' when appending tag');
        }
        foreach ($newTagset as $newTag) {
            if (!(array_key_exists('Key', $newTag) && array_key_exists('Value', $newTag))) {
                throw new \Exception('Invalid Tagset updating: ' . $key . print_r($newTagset, true));
            }
        }

        // add purge tag to signal permanent deletion See: DDPB-2010/OPGOPS-2347
        // get the objects tags and then append with PUT

        $this->log('info', "Retrieving tagset for $key from S3");
        $existingTags = $this->s3Client->getObjectTagging([
            'Bucket' => $this->localBucketName,
            'Key' => $key
        ]);

        $newTagset = array_merge($existingTags['TagSet'], $newTagset);
        $this->log('info', "Tagset retrieved for $key : " . print_r($existingTags, true));
        $this->log('info', "Updating tagset for $key with " . print_r($newTagset, true));

        // Update tags in S3
        $this->s3Client->putObjectTagging([
            'Bucket' => $this->localBucketName,
            'Key' => $key,
            'Tagging' => [
                'TagSet' => $newTagset
            ],
        ]);
        $this->log('info', "Tagset Updated for $key ");
    }

    /**
     * Log message using the internal logger
     *
     * @param $level
     * @param $message
     */
    private function log($level, $message)
    {
        //echo $message."\n"; //enable for debugging reasons. Tail the log with log-level=info otherwise

        $this->logger->log($level, $message, ['extra' => [
            'service' => 's3-storage',
        ]]);
    }
}
