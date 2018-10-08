<?php

namespace AppBundle\Service\Security\LoginAttempts;

abstract class Storage
{
    abstract public function storeAttempt($userId, $timestamp);
}