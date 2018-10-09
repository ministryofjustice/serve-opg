<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\Security\LoginAttempts\BruteForceAttackDetectedException;
use AppBundle\Service\Security\LoginAttempts\MockAbstractStorage;
use Doctrine\ORM\EntityRepository;
use Mockery as m;
use AppBundle\Service\Security\LoginAttempts\AbstractStorage;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->userRepo = m::mock(EntityRepository::class);
        $this->em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')
            ->andReturn($this->userRepo)
            ->getMock();

        $this->storage = m::mock(AbstractStorage::class);

        // user data
        $this->userName = 'username@provider.com';
        $this->user = m::mock(User::class)
            ->shouldReceive('getEmail')->andReturn($this->userName)
            ->getMock();

        // fail event
        $token = m::mock(TokenInterface::class)->shouldReceive('getUser')->andReturn($this->userName)->getMock();
        $this->authenticationFailureEvent = m::mock(AuthenticationFailureEvent::class)
            ->shouldReceive('getAuthenticationToken')
            ->andReturn($token)
            ->getMock();

        // success event
        $token = m::mock(TokenInterface::class)->shouldReceive('getUser')->andReturn($this->user)->getMock();
        $this->successAuthenticationEvent = m::mock(TokenInterface::class)
            ->shouldReceive('getAuthenticationToken')
            ->andReturn($token)
            ->getMock();
    }


    public function testEmptyConfigLoadExistingUser()
    {
        $sut = new UserProvider($this->em, $this->storage);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => $this->userName])->andReturn($this->user);


        $this->assertEquals($this->user, $sut->loadUserByUsername($this->userName));
    }

    public function testEmptyConfigLoadMissingUserThrowsException()
    {
        $sut = new UserProvider($this->em, $this->storage);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => 'nonExisting@provider.com'])->andReturn(false);

        $this->setExpectedException(UsernameNotFoundException::class);
        $this->assertEquals($this->user, $sut->loadUserByUsername('nonExisting@provider.com'));
    }

    public function testBruteForceLockNotReached()
    {
        $this->storage->shouldReceive('hasToWait')->with(5, 100, 200, m::any())->andReturn(false);

        $sut = new UserProvider($this->em, $this->storage, [[5, 100, 200]]);

        $this->userRepo->shouldReceive('findOneBy')->once()->with(['email' => $this->userName])->andReturn($this->user);

        $this->assertEquals($this->user, $sut->loadUserByUsername($this->userName));
    }


    public function testBruteForceLockReached()
    {
        $this->storage->shouldReceive('hasToWait')->with(5, 100, 200, m::any())->andReturn(200);

        $sut = new UserProvider($this->em, $this->storage, [[5, 100, 200]]);


        $this->setExpectedException(BruteForceAttackDetectedException::class);
        $this->userRepo->shouldReceive('findOneBy')->never()->with(['email' => $this->userName]);

        $sut->loadUserByUsername($this->userName);

        $this->assertEquals(200, $this->getExpectedException()->getHasToWaitForSeconds());

    }


    public function testonAuthenticationFailureEmptyConfig()
    {
        $this->storage->shouldReceive('storeAttempt')->never();
        $sut = new UserProvider($this->em, $this->storage, []);
        $sut->onAuthenticationFailure($this->authenticationFailureEvent);
    }

    public function testonAuthenticationFailureStoresAttempt()
    {
        $this->storage->shouldReceive('storeAttempt')->with($this->userName, m::any())->once();
        $sut = new UserProvider($this->em, $this->storage, [[5, 100, 200]]);
        $sut->onAuthenticationFailure($this->authenticationFailureEvent);
    }


    public function tearDown()
    {
        m::close();
    }

}
