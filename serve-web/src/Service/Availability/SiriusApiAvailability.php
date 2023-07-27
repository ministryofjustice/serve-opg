<?php

namespace App\Service\Availability;

use App\Service\SiriusService;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    private SiriusService $siriusService;

    public function __construct(SiriusService $siriusService)
    {
        $this->sirius = $siriusService;
    }

    public function ping()
    {
        try {
           $siriusStatus = $this->sirus->ping();
           $this->isHealthy = $siriusStatus;
        } catch (\Throwable $e) {
            $this->customMessage = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Sirius';
    }
}
