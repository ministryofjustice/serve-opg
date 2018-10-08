<?php

namespace AppBundle\Service\Security\LoginAttempts;

use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

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
//
//    public function isUserLocked($userId)
//    {
//        $attempts = $this->storage->getAttempts($userId);
//        //TODO implement with TDD
//        return false;
//    }

//    public function resetAttempts($userId)
//    {
//        $this->storage->resetAttempts($userId);
//    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $e)
    {
        $username = $e->getAuthenticationToken()->getUser();
        $this->storage->storeAttempt($username, time());
        print_r($this->storage->getAttempts($username));
    }

}