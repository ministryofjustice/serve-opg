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
        return [
            Document::TYPE_COP3 => true,
            Document::TYPE_COP4 => true,
            Document::TYPE_COURT_ORDER => true,
        ];
    }

    protected function isOrderValid()
    {
        return !empty($this->getSubType())
            && !empty($this->getAppointmentType());
    }

}