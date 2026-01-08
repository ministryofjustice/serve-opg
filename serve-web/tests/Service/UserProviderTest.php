<?php

namespace App\Tests\Service;

use App\Common\BruteForceChecker;
use App\Entity\User;
use App\Service\Security\LoginAttempts\AttemptsStorageInterface;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use App\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class UserProviderTest extends MockeryTestCase
{
    use ProphecyTrait;

    private EntityRepository $userRepo;
    private EntityManager $em;
    private AttemptsStorageInterface $storage;
    private BruteForceChecker $bruteForceChecker;
    private string $userName;
    private User $user;
    private LoginFailureEvent $authenticationFailureEvent;
    private TokenInterface $successAuthenticationEvent;

    public function setUp(): void
    {
        $this->user = m::mock(User::class);
        $this->userName = 'username@provider.com';

        $this->userRepo = m::mock(EntityRepository::class);
        $this->em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')
            ->andReturn($this->userRepo)
            ->getMock();

        $this->storage = m::mock(AttemptsStorageInterface::class);
        $this->bruteForceChecker = m::mock(BruteForceChecker::class);

        // fail event
        $badge = m::mock(BadgeInterface::class);
        $badge->shouldReceive('getUserIdentifier')->andReturn($this->userName);

        $passport = m::mock(Passport::class);
        $passport->shouldReceive('hasBadge')->andReturn(true);
        $passport->shouldReceive('getBadge')->andReturn($badge);

        $failureEvent = $this->prophesize(LoginFailureEvent::class);
        $failureEvent->getPassport()->willReturn($passport);
        $this->authenticationFailureEvent = $failureEvent->reveal();

        // success event
        $token = m::mock(TokenInterface::class)->shouldReceive('getUser')->andReturn($this->user);
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

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => 'nonExisting@provider.com'])->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->assertEquals($this->user, $sut->loadUserByIdentifier('nonExisting@provider.com'));
    }

    public function testBruteForceLockNotReached()
    {
        $this->storage->shouldReceive('getAttempts')->with($this->userName)->andReturn([1, 2, 3]);
        $this->bruteForceChecker->shouldReceive('hasToWait')->with([1, 2, 3], 5, 100, 200, m::any())->andReturn(false);

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, [[5, 100, 200]]);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => $this->userName])->andReturn($this->user);

        $this->assertEquals($this->user, $sut->loadUserByIdentifier($this->userName));
    }

    public function testBruteForceLockReached()
    {
        $this->storage->shouldReceive('getAttempts')->with($this->userName)->andReturn([1, 2, 3]);
        $this->bruteForceChecker->shouldReceive('hasToWait')->with([1, 2, 3], 5, 100, 200, m::any())->andReturn(200);

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, [[5, 100, 200]]);

        $this->expectException(BruteForceAttackDetectedException::class);
        $this->userRepo->shouldReceive('findOneBy')->never()->with(['email' => $this->userName]);

        $sut->loadUserByIdentifier($this->userName);
    }

    public function testOnAuthenticationFailureEmptyConfig()
    {
        $this->storage->shouldReceive('storeAttempt')->never();

        $sut = new UserProvider($this->em, $this->storage, $this->bruteForceChecker, []);
        $sut->onAuthenticationFailure($this->authenticationFailureEvent);
    }

    public function testOnAuthenticationFailureStoresAttempt()
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
