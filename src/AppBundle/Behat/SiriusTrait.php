<?php

namespace AppBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

trait SiriusTrait
{
    /**
     * @Then the order documents should be transferred
     */
    public function theOrderDocumentsShouldBeTransferred()
    {
        throw new \RuntimeException();
    }

    /**
     * @Then the order payload should be sent to Sirius
     */
    public function theOrderPayloadShouldBeSentToSirius()
    {
        throw new \RuntimeException();
    }
}
