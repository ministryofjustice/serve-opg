<?php

namespace App\Behat;

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

        $needle = getenv('SIRIUS_S3_BUCKET_NAME');

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
