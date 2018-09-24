<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;

/**
 * SiriusClient wrapper
 */
class SiriusClient extends \Guzzle\Http\Client
{
    /**
     * SiriusClient constructor.
     * @param $args array of arguments
     */
    public function __construct($args)
    {
        var_dump($args);exit;
        parent::__construct($args);
    }
}
