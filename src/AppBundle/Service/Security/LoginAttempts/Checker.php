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

    }

    public function isUserLocked($userId)
    {

    }

    public function resetAttempts()
    {

    }






}