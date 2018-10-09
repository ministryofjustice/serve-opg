<?php

namespace AppBundle\Service\Security\LoginAttempts;

abstract class AbstractStorage
{
    abstract public function storeAttempt($userId, $timestamp);

    abstract public function getAttempts($userId);

    abstract public function resetAttempts($userId);

    public function hasToWait($maxAttempts, $timeRange, $waitFor, $currentTime)
    {
        return false;
    }
}