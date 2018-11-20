<?php

namespace AppBundle\Entity;

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
            Document::TYPE_COURT_ORDER => true,
            Document::TYPE_COP3 => true,
        ];

        // add COP4 if there are no PAs
        if (!$this->hasDeputyByType(Deputy::DEPUTY_TYPE_PA))
        {
            $requiredDocs[Document::TYPE_COP4] = true;
        }

        return $requiredDocs;
    }

    protected function isOrderValid()
    {
        return !empty($this->getSubType())
            && !empty($this->getAppointmentType());
    }
}
