<?php

namespace tests\Service;

use App\Entity\User;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use App\Service\Security\LoginAttempts\MockAttemptsStorage;
use App\Common\BruteForceChecker;
use Doctrine\ORM\EntityRepository;
use Mockery as m;
use App\Service\Security\LoginAttempts\AttemptsStorageInterface;
use App\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserProviderTest extends MockeryTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->userRepo = m::mock(EntityRepository::class);
        $this->em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')
            ->andReturn($this->userRepo)
            ->getMock();

        $this->storage = m::mock(AttemptsStorageInterface::class);
        $this->bruteForceChecker = m::mock(BruteForceChecker::class);

        // user data
        $this->userName = 'username@provider.com';
        $this->user = m::mock(User::class)
            ->shouldReceive('getEmail')
            ->andReturn($this->userName)
            ->getMock();

        // fail event
        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getCredentials')
            ->andReturn(['email' => $this->userName, 'password' => 'fakepass'])
            ->getMock();


        $failureEvent = $this->prophesize(AuthenticationEvent::class);
        $failureEvent->getAuthenticationToken()->willReturn($token);
        $this->authenticationFailureEvent = $failureEvent->reveal();

        // success event
        $token = m::mock(TokenInterface::class)->shouldReceive('getUser')->andReturn($this->user)->getMock();
        $this->successAuthenticationEvent = m::mock(TokenInterface::class)
            ->shouldReceive('getAuthenticationToken')
            ->andReturn($token)
            ->getMock();
    }

    public function testEmptyConfigLoadExistingUser()
    {
        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => $this->userName])->andReturn($this->user);

        $this->assertEquals($this->user, $sut->loadUserByIdentifier($this->userName));
    }

    public function testEmptyConfigLoadMissingUserThrowsException()
    {
        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => 'nonExisting@provider.com'])->andReturn(false);

        $this->expectException(UsernameNotFoundException::class);
        $this->assertEquals($this->user, $sut->loadUserByIdentifier('nonExisting@provider.com'));
    }

    public function testBruteForceLockNotReached()
    {
        $this->storage->shouldReceive('getAttempts')->with($this->userName)->andReturn([1,2,3]);
        $this->bruteForceChecker->shouldReceive('hasToWait')->with([1,2,3], 5, 100, 200, m::any())->andReturn(false);

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, [[5, 100, 200]]);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => $this->userName])->andReturn($this->user);

        $this->assertEquals($this->user, $sut->loadUserByIdentifier($this->userName));
    }

    public function testBruteForceLockReached()
    {
        $this->storage->shouldReceive('getAttempts')->with($this->userName)->andReturn([1,2,3]);
        $this->bruteForceChecker->shouldReceive('hasToWait')->with([1,2,3], 5, 100, 200, m::any())->andReturn(200);

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, [[5, 100, 200]]);

        $this->expectException(BruteForceAttackDetectedException::class);
        $this->userRepo->shouldReceive('findOneBy')->never()->with(['email' => $this->userName]);

        $sut->loadUserByIdentifier($this->userName);

        $this->assertEquals(200, $this->getExpectedException()->getHasToWaitForSeconds());
    }

    public function testonAuthenticationFailureEmptyConfig()
    {
        $this->storage->shouldReceive('storeAttempt')->never();

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, []);
        $sut->onAuthenticationFailure($this->authenticationFailureEvent);
    }

    public function testonAuthenticationFailureStoresAttempt()
    {
        $this->storage->shouldReceive('storeAttempt')
            ->with($this->userName, m::any())
            ->once();

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, [[5, 100, 200]]);
        $sut->onAuthenticationFailure($this->authenticationFailureEvent);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
