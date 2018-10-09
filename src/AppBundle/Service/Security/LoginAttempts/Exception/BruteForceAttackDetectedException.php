<?php

namespace AppBundle\Service\Security\LoginAttempts\Exception;

class BruteForceAttackDetectedException extends \Exception
{
    private $hasToWaitForSeconds;

    /**
     * @param mixed $hasToWaitForSeconds
     */
    public function setHasToWaitForSeconds($hasToWaitForSeconds): void
    {
        $this->hasToWaitForSeconds = $hasToWaitForSeconds;
    }

    /**
     * @return mixed
     */
    public function getHasToWaitForSeconds()
    {
        return $this->hasToWaitForSeconds;
    }

}