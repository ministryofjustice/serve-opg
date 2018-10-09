<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 08/10/2018
 * Time: 17:47
 */

namespace AppBundle\Service\Security\LoginAttempts;


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