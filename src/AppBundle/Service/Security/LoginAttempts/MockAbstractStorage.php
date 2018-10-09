<?php

namespace AppBundle\Service\Security\LoginAttempts;

class MockAbstractStorage extends AbstractStorage
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

}