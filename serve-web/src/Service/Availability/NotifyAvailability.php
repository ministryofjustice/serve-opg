<?php

declare(strict_types=1);

namespace App\Service\Availability;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;

class NotifyAvailability extends ServiceAvailabilityAbstract
{
    private NotifyClient $notifyClient;

    public function __construct(NotifyClient $notifyClient)
    {
        $this->notifyClient = $notifyClient;
        $this->isHealthy = true;
        $this->errors = '';
    }

    public function ping(): void
    {
        try {
            $this->pingNotify();
        } catch (NotifyException $e) {
            $this->isHealthy = false;
            $this->errors = sprintf('Notify - %s', $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'Notify';
    }

    public function pingNotify(): ?array
    {
        return $this->notifyClient->listTemplates();
    }
}
