<?php

namespace AppBundle\Service\Security\LoginAttempts;

interface AttemptsStorageInterface
{
    /**
     * @param string $userId
     * @param integer $timestamp
     */
    public function storeAttempt($userId, $timestamp);

    /**
     * @param string $userId
     *
     * @return array of timestamps with the attempts for that userId
     */
    public function getAttempts($userId);

    /**
     * @param string $userId
     */
    public function resetAttempts($userId);

}