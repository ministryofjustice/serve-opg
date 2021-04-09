<?php

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\SuccessfulAuthenticationListener;
use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class SuccessfulAuthenticationListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        self::assertEquals(
            SuccessfulAuthenticationListener::getSubscribedEvents(),
            ['security.authentication.success' => 'updateLastLoginAt']
        );
    }

    public function testUpdateLastLoginAt()
    {
        $expectedUserModel = new User('some@email.adress.com');
        $expectedUserModel->setLastLoginAt(new DateTime());

        /** @var EntityManager|ObjectProphecy $entityManager */
        $entityManager = self::prophesize(EntityManager::class);
        $entityManager->persist($expectedUserModel)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $dummyToken = new UsernamePasswordToken($expectedUserModel, 'bar', 'key');
        $authenticationEvent = new AuthenticationEvent($dummyToken);

        $eventListener = new SuccessfulAuthenticationListener($entityManager->reveal());
        $eventListener->updateLastLoginAt($authenticationEvent);
    }
}
