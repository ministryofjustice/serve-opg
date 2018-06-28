<?php

namespace AppBundle\Behat;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{


    public function __construct($options = [])
    {
    }

    public function setKernel(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

}
