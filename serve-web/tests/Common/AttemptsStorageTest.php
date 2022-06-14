<?php

namespace tests\Common;

use App\Common\BruteForceChecker;
use PHPUnit\Framework\TestCase;

class AttemptsStorageTest extends TestCase
{
    private $sut;

    public function setUp(): void
    {
        $this->sut = new BruteForceChecker();
    }

    public static function hasToWaitProvider()
    {
        $attempts = [1, 2, 3, 4, 5, 6, 1001, 1002, 1003];

        return [
            // 3 attempts in a 10 seconds range locks for 500 seconds. In this case the lock at 506 expired, and only from 1003 there is a lock until 1503
            [$attempts, 3, 10, 500, 1203, 1003+500-1203],
            // same as above, lock just expired
            [$attempts, 3, 10, 500, 1503, false],
            // first powerful lock still active, the second is not reached
            [$attempts, 6, 10, 10000, 1003, 6+10000-1003],
            // no locks reached yet
            [$attempts, 10, 10, 10000, 1003, false],

            // zero or one attempts, or rules set to 1 attempt only => never lock
            [[], 5, 60, 600, 100, false],
            [[1], 0, 60, 600, 0, false],
            [[1], 1, 60, 600, 0, false],
            [$attempts, 0, 60, 600, 0, false],
            [$attempts, 1, 60, 600, 0, false],
        ];
    }

    /**
     * @dataProvider hasToWaitProvider
     */
    public function testHasToWait($attemptTimeStamps, $maxAttempts, $timeRange, $lockFor, $currentTime, $expectedWaitFor)
    {
        $actual =  $this->sut->hasToWait($attemptTimeStamps, $maxAttempts, $timeRange, $lockFor, $currentTime);
        $this->assertEquals($expectedWaitFor, $actual);
    }
}
