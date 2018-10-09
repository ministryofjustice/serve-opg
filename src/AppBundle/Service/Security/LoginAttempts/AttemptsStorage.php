<?php

namespace AppBundle\Service\Security\LoginAttempts;

abstract class AttemptsStorage
{
    private $attempts;

    public function __construct()
    {
        $this->attempts = [];
    }

    public function storeAttempt($userId, $timestamp)
    {
        if (!isset($this->attempts[$userId])) {
            $this->attempts[$userId] = [];
        }

        $this->attempts[$userId] = $timestamp;
    }

    public function getAttempts($userId)
    {
        return $this->attempts[$userId];
    }

    public function resetAttempts($userId)
    {
        unset($this->attempts[$userId]);
    }

    public function hasToWait($maxAttempts, $timeRange, $waitFor, $currentTime)
    {
        return false;
    }
}