<?php

namespace App\Security;

use App\Common\BruteForceChecker;
use App\Entity\User;
use App\Service\Security\LoginAttempts\AttemptsStorageInterface;
use App\Service\Security\LoginAttempts\Exception\BruteForceAttackDetectedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private const string LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly BruteForceChecker $bruteForceChecker,
        private readonly AttemptsStorageInterface $storage,
        private readonly array $rules = [],
    ) {
    }

    /**
     * @throws \Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        // reset login attempts by this user
        if (!is_null($user)) {
            $userId = $user->getId();
            $this->storage->resetAttempts($userId);
        }

        // redirect to correct path
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // store the failed login attempt
        /** @var ?string $username */
        $username = $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);

        if (!is_null($username)) {
            $this->storage->storeAttempt($username, time());
        }

        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        // redirect to login
        return new RedirectResponse($this->getLoginUrl($request));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * @throws BruteForceAttackDetectedException
     */
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        $waitFor = $this->usernameLockedForSeconds($email);
        if (false !== $waitFor) {
            throw new BruteForceAttackDetectedException();
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    /**
     * Return the highest timestamp when the user can be unlocked,
     * based on the rules and previous attempts from the same username, stored in the storage (e.g. dynamoDb).
     */
    public function usernameLockedForSeconds(string $username): bool|int
    {
        $waits = [];
        foreach ($this->rules as $rule) {
            // fetch all the rules and check if for any of those, the user has to wait
            list($maxAttempts, $timeRange, $waitFor) = $rule;

            $waitFor = $this->bruteForceChecker->hasToWait(
                $this->storage->getAttempts($username),
                $maxAttempts,
                $timeRange,
                $waitFor,
                time()
            );

            if ($waitFor) {
                $waits[] = $waitFor;
            }
        }

        return $waits ? max($waits) : false;
    }
}
