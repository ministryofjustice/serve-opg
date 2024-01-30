<?php

namespace App\Service\Availability;

/**
 * TODO change into an interface with
 * isHealthy
 * getErrorsß
 */
abstract class ServiceAvailabilityAbstract
{
    protected bool $isHealthy;

    protected string $errors;

    protected ?string $customMessage = null;

    public function isHealthy(): bool
    {
        return $this->isHealthy;
    }

    public function getErrors(): string
    {
        return $this->errors;
    }

    public function getCustomMessage()
    {
        return $this->customMessage;
    }

    public function toArray(): array
    {
        return [
            'healthy' => $this->isHealthy(),
            'errors' => $this->getErrors(),
        ];
    }

    abstract public function getName(): string;

    abstract public function ping();
}
