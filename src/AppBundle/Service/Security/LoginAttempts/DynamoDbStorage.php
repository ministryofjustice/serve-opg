<?php

namespace AppBundle\Service\Security\LoginAttempts;

class DynamoDbStorage extends Storage
{
    public function storeAttempt($userId, $timestamp)
    {
        // TODO: Implement storeAttempt() method.
    }

}