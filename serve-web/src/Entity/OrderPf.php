<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OrderPf extends Order
{
    public function getType(): string
    {
        return Order::TYPE_PF;
    }

    public function getAcceptedDocumentTypes(): array
    {
        $requiredDocs = [
            Document::TYPE_COURT_ORDER => true
        ];

        if ($this->getSubType() !== order::SUBTYPE_INTERIM_ORDER) {
            $requiredDocs[Document::TYPE_COP1A] = true;
            $requiredDocs[Document::TYPE_COP3] = true;

            // add COP4 if there are no PAs
            if (!$this->hasDeputyByType(Deputy::DEPUTY_TYPE_PA)) {
                $requiredDocs[Document::TYPE_COP4] = true;
            }
        }

        return $requiredDocs;
    }

    public function isOrderValid(): bool
    {
        return !empty($this->getSubType()) &&
            !empty($this->getAppointmentType()) &&
            !empty($this->getHasAssetsAboveThreshold());
    }
}
