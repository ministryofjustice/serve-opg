<?php

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\SuccessfulAuthenticationListener;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class SuccessfulAuthenticationListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        self::assertEquals(
            ['security.authentication.success' => 'updateLastLoginAt'],
            SuccessfulAuthenticationListener::getSubscribedEvents()
        );
    }

    public function testUpdateLastLoginAt()
    {
        $expectedUserModel = new User('some@email.adress.com');
        $expectedUserModel->setLastLoginAt(new \DateTime());

        $entityManager = self::createMock(EntityManager::class);
        $entityManager->expects(self::once())->method('persist')->with($expectedUserModel);
        $entityManager->expects(self::once())->method('flush');

        $dummyToken = new UsernamePasswordToken($expectedUserModel, 'bar', ['key']);
        $authenticationEvent = new AuthenticationEvent($dummyToken);

        $eventListener = new SuccessfulAuthenticationListener($entityManager);

        $eventListener->updateLastLoginAt($authenticationEvent);
    }
}
