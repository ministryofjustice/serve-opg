<?php

namespace AppBundle\Service;

use AppBundle\Service\Security\LoginAttempts\AttemptsStorage;

class AttemptsStorageTest extends \PHPUnit_Framework_TestCase
{
    public static function hasToWaitProvider()
    {
        return [
            // no rules => never locked
            [[], 5, 60, 600, 100, false],
            // one attempt => never locked
            [[0], 0, 60, 600, 0, false],
            [[0], 1, 60, 600, 0, false],
            // 2 attempts reached in last 500 seconds at time 1500 => max was 3. not locked
            [[0, 100, 1200, 1300], 3, 500, 5000, 1500, false],
            // 3 attempts reached in last 500 seconds at time 1500 => locked for 5000 seconds
            [[0, 100, 1200, 1300, 1500], 3, 500, 5000, 1500, 5000],
            // after 500 seconds, still locked for 4500 seconds
            [[0, 100, 1200, 1300, 1500], 3, 500, 5000, 2000, 4500],
            // after 5001 seconds, unlocked
            [[0, 100, 1200, 1300, 1500], 3, 500, 5000, 2000, 4500],
        ];
    }

    /**
     * @dataProvider hasToWaitProvider
     */
    public function testhasToWait($attemptTimeStamps, $maxAttempts, $timeRange, $waitFor, $currentTime, $expectedWaitFor)
    {
        $sut = new AttemptsStorage();
        foreach($attemptTimeStamps as $attemptTimeStamp) {
            $sut->storeAttempt('userid', $attemptTimeStamp);
        }

        $actual = $sut->hasToWait($maxAttempts, $timeRange, $waitFor, $currentTime);
        $this->assertEquals($expectedWaitFor, $actual);

    }

}
