<?php

namespace App\Behat;

trait SiriusTrait
{
    /**
     * @Then the documents for order :orderIdentifier should be transferred
     */
    public function theDocumentsForOrderShouldBeTransferred(string $orderIdentifier): void
    {
        $documentsList = explode("|", $this->getDocumentsList($orderIdentifier));

        $needle = getenv('SIRIUS_S3_BUCKET_NAME');

        foreach ($documentsList as $docLocation) {
            if (!strpos($docLocation, $needle)) {
                throw new \RuntimeException('Document location ' . $docLocation   . ' does not contain ' . $needle);
            }
        }
    }

    private function getDocumentsList(string $orderIdentifier): ?string
    {
        $this->visit('/behat/document-list/'.$orderIdentifier);

        return $this->getSession()->getPage()->getContent();
    }
}
