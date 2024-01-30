<?php

namespace App\Service\Security\LoginAttempts;

interface AttemptsStorageInterface
{
    public function storeAttempt(string $userId, int $timestamp);

    public function getAttempts(string $userId): array;

    public function resetAttempts(string $userId);
}
