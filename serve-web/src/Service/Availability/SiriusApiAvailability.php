<?php

namespace App\Service\Availability;

use App\Service\SiriusService;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    private SiriusService $siriusService;

    public function __construct(SiriusService $siriusService)
    {
        $this->siriusService = $siriusService;
    }

    public function ping(): void
    {
        try {
           $siriusStatus = $this->siriusService->ping();
           $this->isHealthy = $siriusStatus;
        } catch (\Throwable $e) {
            $this->isHealthy = false;
            $this->customMessage = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Sirius';
    }
}
