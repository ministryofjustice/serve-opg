<?php

namespace AppBundle\Entity;

class OrderPf extends Order
{
    /**
     * @return string
     */
    public function getType()
    {
        return Order::TYPE_PF;
    }

    public function getAcceptedDocumentTypes()
    {
        $requiredDocs = [
            Document::TYPE_COP1A => true,
            Document::TYPE_COP3 => true
        ];

        // remove COP4 if a PA deputy exists for this order
        if (!$this->hasDeputyByType(Deputy::DEPUTY_TYPE_PA))
        {
            $requiredDocs[Document::TYPE_COP4] = true;
        }

        $requiredDocs[Document::TYPE_COURT_ORDER] = true;

        return $requiredDocs;
    }

    protected function isOrderValid()
    {
        return !empty($this->getSubType())
            && !empty($this->getAppointmentType())
            && !empty($this->getHasAssetsAboveThreshold());
    }

}
