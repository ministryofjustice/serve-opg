<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Mockery as m;
use AppBundle\Service\Security\LoginAttempts\Storage;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->userRepo = m::mock(EntityRepository::class);
        $this->em = m::mock(EntityManager::class)
            ->shouldReceive('getRepository')
            ->andReturn($this->userRepo)
            ->getMock();

        $this->storage = m::mock(Storage::class);

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


    public function testEmptyConfigDoesNotAlterBehaviour()
    {
        $sut = new UserProvider($this->em, $this->storage);

        for ($attempts = 0; $attempts < 5; $attempts++) {
            $sut->onAuthenticationFailure($this->authenticationFailureEvent);
        }

        $this->userRepo->shouldReceive('findOneBy')->with(['email'=>$this->userName])->andReturn($this->user);

        $this->assertEquals($this->user, $sut->loadUserByUsername($this->userName));
    }

    public function tearDown()
    {
        m::close();
    }

}
