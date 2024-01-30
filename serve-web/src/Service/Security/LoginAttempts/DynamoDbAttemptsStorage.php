<?php

namespace App\Service\Security\LoginAttempts;

use App\Common\SessionConnectionCreatingTable;

class DynamoDbAttemptsStorage implements AttemptsStorageInterface
{
    private SessionConnectionCreatingTable $connection;

    public function __construct(SessionConnectionCreatingTable $connection)
    {
        $this->connection = $connection;
    }

    public function storeAttempt(string $userId, int $timestamp): void
    {
        $data = $this->getAttempts($userId);
        $data[] = $timestamp;

        $this->connection->write($userId, json_encode($data), true);
    }

    public function getAttempts(string $userId): array
    {
        $data = $this->connection->read($userId)['data'] ?? null;
        if (null === $data) {
            return [];
        }

        return json_decode($data, true);
    }

    public function resetAttempts($userId)
    {
        return $this->connection->delete($userId);
    }
}
