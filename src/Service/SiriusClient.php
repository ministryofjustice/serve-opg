<?php

namespace App\Service;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * SiriusClient wrapper
 */
class SiriusClient extends GuzzleHttpClient
{
    /**
     * SiriusClient constructor.
     * @param $args array of arguments
     */
    public function __construct($args, $headers)
    {
        // see https://github.com/symfony/yaml/commit/9744e5991d1436620c3d01b507160b31803546f3#diff-037865b8555f7db5ac9338cfb5be7466
        $args['headers'] = $headers;
        parent::__construct($args);
    }
}
