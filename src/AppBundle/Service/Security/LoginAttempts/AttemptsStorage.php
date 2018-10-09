<?php

namespace AppBundle\Service\Security\LoginAttempts;

class AttemptsStorage
{
    private $attempts;

    public function __construct()
    {
        $this->attempts = [];
    }

    public function storeAttempt($userId, $timestamp)
    {
        if (!isset($this->attempts[$userId])) {
            $this->attempts[$userId] = [];
        }

        $this->attempts[$userId][] = $timestamp;
    }

    /**
     * @param string $userId
     *
     * @return array of timestamps with the attempts for that userId
     */
    public function getAttempts($userId)
    {
        return $this->attempts[$userId] ?: [];
    }

    public function resetAttempts($userId)
    {
        unset($this->attempts[$userId]);
    }

    /**
     * @param string $userId
     * @param integer $maxAttempts
     * @param integer $timeRange seconds
     * @param integer $lockFor seconds
     * @param integer $currentTime timestamp (seconds)
     *
     * @return bool
     */
    public function hasToWait($userId, $maxAttempts, $timeRange, $lockFor, $currentTime)
    {
        $userAttempts = $this->getAttempts($userId);
        $userAttemptsCount = count($userAttempts);
        if (empty($userAttempts) || count($userAttempts) <= 1 || $maxAttempts<=1 || $userAttemptsCount < $maxAttempts) {
            return false;
        }

        $locks = [];
        // cycle the user attempts in groups of size of $maxAttempts, in order to find out group causing locks
        for ($index = 0; $index <= $userAttemptsCount - $maxAttempts; $index++) {
            $attemptsGroup = array_slice($userAttempts, $index, $maxAttempts);
            $lastAttemptAt = $attemptsGroup[$maxAttempts - 1];
            $from = $lastAttemptAt - $timeRange;
            $attemptsInRange = array_filter($attemptsGroup, function ($el) use ($from) {
                return $el >= $from;
            });
            if (count($attemptsInRange) === $maxAttempts) {
                $locks[] = $lastAttemptAt + $lockFor;
            }
        }

        $highestLock = max($locks);

        return $currentTime < $highestLock
            ?   $highestLock - $currentTime
            : false;
    }
}