<?php

namespace AppBundle\Behat;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext
{
    use RegionTrait;

    public function __construct($options = [])
    {
    }

    public function setKernel(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

}
