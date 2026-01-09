<?php

namespace App\Service\Security\LoginAttempts\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BruteForceAttackDetectedException extends AuthenticationException
{
}
