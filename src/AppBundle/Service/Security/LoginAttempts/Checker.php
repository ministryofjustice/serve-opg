<?php

namespace AppBundle\Service\Security\LoginAttempts;

class Checker
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var array
     */
    private $attemptsConfig;

    /**
     * Checker constructor.
     * @param Storage $storage
     */
    public function __construct(Storage $storage, $attemptsConfig = [])
    {
        $this->storage = $storage;
    }

    public function registerUserLoginFailure($userId, $timestamp)
    {
        $this->storage->storeAttempt($userId, $timestamp);

//        print_r($this->storage->getAttempts($userId));

    }

    public function isUserLocked($userId)
    {
        //TODO
        return false;
    }

    public function resetAttempts($userId)
    {
        $this->storage->resetAttempts($userId);
    }


}