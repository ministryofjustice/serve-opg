<?php

declare(strict_types=1);

namespace App\Service\Availability;

use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\NotifyException;

class NotifyAvailability extends ServiceAvailabilityAbstract
{
    private Client $notifyClient;

    public function __construct(Client $notifyClient)
    {
        $this->notifyClient = $notifyClient;
        $this->isHealthy = true;
        $this->errors = '';
    }

    public function ping(): void
    {
        try {
            $this->notifyClient->listTemplates();
        } catch (NotifyException $e) {
            $this->isHealthy = false;
            $this->errors = sprintf('Notify - %s', $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'Notify';
    }
}
