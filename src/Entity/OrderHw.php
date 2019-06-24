<?php

namespace App\Entity;

class OrderHw extends Order
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_HW;
    }

    public function getAcceptedDocumentTypes()
    {
        $requiredDocs = [
            Document::TYPE_COURT_ORDER => true
        ];

        if ($this->getSubType() !== order::SUBTYPE_INTERIM_ORDER) {
            $requiredDocs[Document::TYPE_COP3] = true;

            // add COP4 if there are no PAs
            if (!$this->hasDeputyByType(Deputy::DEPUTY_TYPE_PA)) {
                $requiredDocs[Document::TYPE_COP4] = true;
            }
        }

        return $requiredDocs;
    }

    public function isOrderValid()
    {
        return !empty($this->getSubType()) &&
            !empty($this->getAppointmentType());
    }
}
