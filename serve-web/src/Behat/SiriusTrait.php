<?php

namespace App\Behat;

trait SiriusTrait
{
    /**
     * @Then the documents for order :orderIdentifier should be transferred
     *
     * @param null|string $orderIdentifier
     */
    public function theDocumentsForOrderShouldBeTransferred($orderIdentifier): void
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
     * @param null|string $orderIdentifier
     */
    private function getDocumentsList($orderIdentifier): ?string
    {
        $this->visit('/behat/document-list/'.$orderIdentifier);

        return $this->getSession()->getPage()->getContent();
    }
}
