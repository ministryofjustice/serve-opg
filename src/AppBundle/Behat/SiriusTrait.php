<?php

namespace AppBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;

trait SiriusTrait
{
    /**
     * @Then the documents for order :orderIdentifier should be transferred
     */
    public function theDocumentsForOrderShouldBeTransferred($orderIdentifier)
    {
        $documentsList = explode("|", $this->getDocumentsList($orderIdentifier));

        /** @to-do use ENV variable for needle */
        $needle = 'sirius_test_bucket';

        foreach ($documentsList as $docLocation) {
            if (!strpos($docLocation, $needle)) {
                throw new \RuntimeException('Document location ' . $docLocation   . ' does not contain ' . $needle);
            }
        }
    }

    /**
     * @param bool $throwExceptionIfNotFound
     * @param int  $index                    = last (default), 1=second last
     *
     * @return array|null
     */
    private function getDocumentsList($orderIdentifier)
    {
        $this->visit('/behat/document-list/' . $orderIdentifier);

        return $this->getSession()->getPage()->getContent();
    }

}
