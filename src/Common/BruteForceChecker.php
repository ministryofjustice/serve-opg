<?php

namespace Common;

class BruteForceChecker
{
    /**
     * @param array $userAttempts [timestamp1, timestamp2, ...]
     * @param integer $maxAttempts
     * @param integer $timeRange seconds
     * @param integer $lockFor seconds
     * @param integer $currentTime timestamp (seconds)
     *
     * @return bool|int
     */
    public function hasToWait(array $userAttempts, $maxAttempts, $timeRange, $lockFor, $currentTime)
    {
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
            if (count($attemptsInRange) === $maxAttempts && $currentTime <= $lastAttemptAt + $lockFor) {
                $locks[] = $lastAttemptAt + $lockFor - $currentTime;
            }
        }

        return $locks ? max($locks) : false;
    }
}
