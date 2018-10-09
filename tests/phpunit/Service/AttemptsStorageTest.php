<?php

namespace AppBundle\Service;

use AppBundle\Service\Security\LoginAttempts\AttemptsStorage;

class AttemptsStorageTest extends \PHPUnit_Framework_TestCase
{
    public static function hasToWaitProvider()
    {
        return [
            [[], 5, 60, 600, 100, false],
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
