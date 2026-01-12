<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Common\BruteForceChecker;
use App\Security\LoginFormAuthenticator;
use App\Service\Security\LoginAttempts\AttemptsStorageInterface;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginFormAuthenticatorTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private BruteForceChecker $bruteForceChecker;
    private AttemptsStorageInterface $storage;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->bruteForceChecker = $this->createMock(BruteForceChecker::class);
        $this->storage = $this->createMock(AttemptsStorageInterface::class);
    }

    public function testAuthenticateBruteForceLockReached(): void
    {
        $email = 'testauthenticateuserbad@foo.bar';

        $attemptsSoFar = [1];

        $sut = new LoginFormAuthenticator(
            $this->entityManager,
            $this->urlGenerator,
            $this->csrfTokenManager,
            $this->bruteForceChecker,
            $this->storage,
            [[5, 100, 200]]
        );

        $session = self::createMock(SessionInterface::class);
        $session->expects(self::once())
            ->method('set')
            ->with(SecurityRequestAttributes::LAST_USERNAME, $email);

        $request = self::createMock(Request::class);
        $request->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        $request->request = new InputBag();
        $request->request->add(['email' => $email, 'password' => '', '_csrf_token' => '']);

        $this->storage->expects(self::once())
            ->method('getAttempts')
            ->with($email)
            ->willReturn($attemptsSoFar);

        $this->bruteForceChecker->expects(self::once())
            ->method('hasToWait')
            ->with(
                $attemptsSoFar,
                5,
                100,
                200,
                self::isType('int')
            )
            ->willReturn(200);

        self::expectException(BruteForceAttackDetectedException::class);

        $sut->authenticate($request);
    }

    public function testAuthenticateBruteForceLockNotReached(): void
    {
        $email = 'testauthenticateuser@foo.bar';
        $attemptsSoFar = [1];

        $sut = new LoginFormAuthenticator(
            $this->entityManager,
            $this->urlGenerator,
            $this->csrfTokenManager,
            $this->bruteForceChecker,
            $this->storage,
            [[5, 100, 200]]
        );

        $session = self::createMock(SessionInterface::class);
        $session->expects(self::once())
            ->method('set')
            ->with(SecurityRequestAttributes::LAST_USERNAME, $email);

        $request = self::createMock(Request::class);
        $request->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        $request->request = new InputBag();
        $request->request->add(['email' => $email, 'password' => '', '_csrf_token' => '']);

        $this->storage->expects(self::once())
            ->method('getAttempts')
            ->with($email)
            ->willReturn($attemptsSoFar);

        $this->bruteForceChecker->expects(self::once())
            ->method('hasToWait')
            ->with(
                $attemptsSoFar,
                5,
                100,
                200,
                self::isType('int')
            )
            ->willReturn(false);

        $result = $sut->authenticate($request);

        self::assertInstanceOf(Passport::class, $result);
    }

    public function testOnAuthenticationSuccessNonNullUser(): void
    {
        $sut = new LoginFormAuthenticator(
            $this->entityManager,
            $this->urlGenerator,
            $this->csrfTokenManager,
            $this->bruteForceChecker,
            $this->storage,
            [[5, 100, 200]]
        );

        $userIdentifier = 'foomo.barmo@foo.bar';

        $request = self::createMock(Request::class);
        $session = self::createMock(SessionInterface::class);
        $token = self::createMock(TokenInterface::class);
        $user = self::createMock(UserInterface::class);

        $this->storage->expects(self::once())->method('resetAttempts')->with($userIdentifier);
        $this->urlGenerator->expects(self::once())->method('generate')->with('homepage')->willReturn('/');
        $request->expects(self::once())->method('getSession')->willReturn($session);
        $session->expects(self::once())->method('get')->with(self::anything())->willReturn(null);
        $user->expects($this->once())->method('getUserIdentifier')->willReturn($userIdentifier);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $response = $sut->onAuthenticationSuccess($request, $token, 'main');

        self::assertEquals('/', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailureStoresAttempt(): void
    {
        $sut = new LoginFormAuthenticator(
            $this->entityManager,
            $this->urlGenerator,
            $this->csrfTokenManager,
            $this->bruteForceChecker,
            $this->storage,
            [[5, 100, 200]]
        );

        $email = 'moo.bar@foo.bar';

        $request = self::createMock(Request::class);
        $session = self::createMock(SessionInterface::class);
        $exception = new BruteForceAttackDetectedException();

        $request->expects(self::once())->method('getSession')->willReturn($session);
        $session->expects(self::once())->method('get')->with(SecurityRequestAttributes::LAST_USERNAME)->willReturn($email);
        $session->expects(self::once())->method('set')->with(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        $this->storage->expects(self::once())->method('storeAttempt')->with($email, self::isType('int'));
        $this->urlGenerator->expects(self::once())->method('generate')->with('app_login')->willReturn('/login');

        $response = $sut->onAuthenticationFailure($request, $exception);

        self::assertEquals('/login', $response->getTargetUrl());
    }
}
