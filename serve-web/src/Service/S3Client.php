<?php

namespace App\Service;

/**
 *  S3Client wrapper allowing local S3 client config with custom endpoint
 * e.g. fake S3
 */
class S3Client extends \Aws\S3\S3Client
{
    /**
     * S3Client constructor.
     * @param $s3Region e.g. us-west-1
     * @param $s3Endpoint leave empty for instances with AWS credentials. Set for local env like fakes3
     */
    public function __construct(string $s3Region, ?string $s3Endpoint)
    {
        $args = [
            'version' => 'latest',
            'region' => $s3Region,
        ];

        // custom endpoint e.g. fakes3
        if ($s3Endpoint) {
            $args += [
                'use_path_style_endpoint' => true,
                'endpoint' => $s3Endpoint,
                'validate' => false
            ];
        } else { // AWS credentials available in the instance
            $args += [
                'validate' => true,
            ];
        }

        parent::__construct($args);
    }
}
