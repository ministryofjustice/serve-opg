<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Entity\User;
use App\Service\OrderService;
use App\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/behat')]
class BehatController extends AbstractController
{
    public const BEHAT_USERS = [
        ['email' => 'behat@digital.justice.gov.uk', 'admin' => false],
        ['email' => 'behat+user-management@digital.justice.gov.uk', 'admin' => false],
        ['email' => 'behat+admin@digital.justice.gov.uk', 'admin' => true],
    ];

    // keep in sync with behat-cases.csv
    public const BEHAT_CASE_NUMBER = '93559316';
    public const BEHAT_INTERIM_CASE_NUMBER = '93559317';

    /**
     * @string behatPassword
     */
    private string $behatPassword;

    public function __construct(
        private readonly EntityManager $em,
        private readonly OrderService $orderService,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly UserProvider $userProvider,
    ) {
        $this->behatPassword = getenv('BEHAT_PASSWORD');
    }

    /**
     * throw a AccessDeniedException if DC_BEHAT_CONTROLLER_ENABLED is empty or false.
     */
    private function securityChecks(): void
    {
        if (!getenv('DC_BEHAT_CONTROLLER_ENABLED')) {
            throw new AccessDeniedException('Not accessible on this environment');
        }
    }

    #[Route(path: '/behat-user-upsert')]
    public function userUpsert(Request $request): Response
    {
        $this->securityChecks();

        foreach (self::BEHAT_USERS as $userDetails) {
            $email = $userDetails['email'];
            $isAdmin = $userDetails['admin'];

            // add user if not existing
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $user = new User($email);

                if ($isAdmin) {
                    $user->setRoles(['ROLE_ADMIN']);
                }

                $ret = 'User '.$email.' created';
            } else {
                $ret = 'User '.$email.' already present, password reset';
            }

            $user->setPassword($this->hasher->hashPassword($user, $this->behatPassword));

            $this->em->persist($user);
            $this->em->flush();
        }

        return new Response($ret);
    }

    #[Route(path: '/reset-behat-test-users')]
    public function resetBehatTestUsersAction(EntityManagerInterface $entityManager): Response
    {
        $this->securityChecks();

        foreach (self::BEHAT_USERS as $userDetails) {
            $user = $this->em->getRepository(User::class)->findOneByEmail($userDetails['email']);

            if ($user) {
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }

        return new Response('Test users reset');
    }

    #[Route(path: '/reset-behat-orders')]
    public function resetBehatOrdersAction(Request $request): Response
    {
        $this->securityChecks();

        $ret = [];

        // empty orders for behat clients
        $behatCases = [self::BEHAT_CASE_NUMBER, self::BEHAT_INTERIM_CASE_NUMBER];
        $clients = $this->em->getRepository(Client::class)->findBy(['caseNumber' => $behatCases]);
        /** @var Client $client */
        foreach ($clients as $client) {
            $clientOrders = $this->em->getRepository(Order::class)->findBy(['client' => $client]);
            /** @var Order $order */
            foreach ($clientOrders as $order) {
                $this->orderService->emptyOrder($order);
                $ret[] = get_class($order).' for client '.$client->getCaseNumber().' present and emptied (docs, deputies)';
            }
        }

        return new Response(implode("\n", array_filter($ret)));
    }

    #[Route(path: '/reset-brute-force-attempts-logger')]
    public function resetBruteForceAction(Request $request): Response
    {
        $this->securityChecks();

        foreach (self::BEHAT_USERS as $user) {
            $this->userProvider->resetUsernameAttempts($user['email']);
        }

        return new Response('attempts reset done');
    }

    #[Route(path: '/document-list/{orderIdentifier}')]
    public function orderDocumentsList(Request $request, mixed $orderIdentifier): Response
    {
        $this->securityChecks();

        $ret = [];

        // get Order for behat case
        $order = $this->getOrderFromIdentifier($orderIdentifier);

        $documents = $order->getDocuments();
        foreach ($documents as $document) {
            $ret[] = $document->getRemoteStorageReference();
        }

        return new Response(implode('|', array_filter($ret)));
    }

    private function getOrderFromIdentifier(string $orderIdentifier)
    {
        list($caseNumber, $orderType) = explode('-', $orderIdentifier);

        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => self::BEHAT_CASE_NUMBER]);

        $repo = 'PF' == $orderType ? OrderPf::class : OrderHw::class;

        return $this->em->getRepository($repo)->findOneBy(['client' => $client->getId()]);
    }

    #[Route(path: '/reset-database')]
    public function resetDatabase(KernelInterface $kernel): Response
    {
        $this->securityChecks();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--purge-with-truncate' => null,
            '-n' => null,
        ]);

        $output = new BufferedOutput();

        try {
            $content = $output->fetch();
            $application->run($input, $output);

            return new Response($content);
        } catch (\Throwable $e) {
            return new Response($e->getMessage());
        }
    }
}
