<?php

namespace App\Service\Security\LoginAttempts;

interface AttemptsStorageInterface
{
    public function storeAttempt(string $userId, int $timestamp);

    /**
     * @return int[] array of timestamps when attempt(s) were made
     */
    public function getAttempts(string $userId): array;

    public function resetAttempts(string $userId);
}
