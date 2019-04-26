<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\OrderHw;
use App\Entity\OrderPf;
use App\Entity\User;
use App\Service\ClientService;
use App\Service\MailSender;
use App\Service\OrderService;
use App\Service\Security\LoginAttempts\UserProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/behat")
 */
class BehatController extends Controller
{
    const BEHAT_EMAIL = 'behat@digital.justice.gov.uk';
    const BEHAT_PASSWORD = 'Abcd1234';
    // keep in sync with behat-cases.csv
    const BEHAT_CASE_NUMBER = '93559316';
    const BEHAT_INTERIM_CASE_NUMBER = '93559317';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * BehatController constructor.
     * @param EntityManager $em
     * @param ClientService $clientService
     * @param OrderService $orderService
     * @param UserPasswordEncoderInterface $encoder
     * @param UserProvider $userProvider
     */
    public function __construct(EntityManager $em, ClientService $clientService, OrderService $orderService, UserPasswordEncoderInterface $encoder, UserProvider $userProvider)
    {
        $this->em = $em;
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->encoder = $encoder;
        $this->userProvider = $userProvider;
    }

    /**
     * throw a AccessDeniedException if DC_BEHAT_CONTROLLER_ENABLED is empty or false
     */
    private function securityChecks()
    {
        if (!getenv('DC_BEHAT_CONTROLLER_ENABLED')) {
            throw new AccessDeniedException('Not accessible on this environment');
        }
    }

    /**
     * @Route("/behat-user-upsert")
     */
    public function userUpsert(Request $request)
    {
        $this->securityChecks();

        // add user if not existing
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::BEHAT_EMAIL]);
        if (!$user) {
            $user = new User(self::BEHAT_EMAIL);
            $this->em->persist($user);
            $ret = "User " . self::BEHAT_EMAIL . " created";
        } else {
            $ret = "User " . self::BEHAT_EMAIL . " already present, password reset";
        }

        $encodedPassword = $this->encoder->encodePassword($user, self::BEHAT_PASSWORD);
        $user->setPassword($encodedPassword);

        $this->em->flush($user);

        return new Response($ret);
    }

    /**
     * @Route("/reset-behat-orders")
     */
    public function resetBehatOrdersAction(Request $request)
    {
        $this->securityChecks();

        $ret = [];

        // empty orders for behat clients
        $behatCases = [self::BEHAT_CASE_NUMBER, self::BEHAT_INTERIM_CASE_NUMBER];
        $clients = $this->em->getRepository(Client::class)->findBy(['caseNumber' => $behatCases]);
        /** @var Client $client */
        foreach($clients as $client) {
            $clientOrders = $this->em->getRepository(Order::class)->findBy(['client' => $client]);
            /** @var Order $order */
            foreach ($clientOrders as $order) {
                $this->orderService->emptyOrder($order);
                $ret[] = get_class($order) . " for client " . $client->getCaseNumber() . " present and emptied (docs, deputies)";
            }
        }

        return new Response(implode("\n", array_filter($ret)));
    }

    /**
     * @Route("/reset-brute-force-attempts-logger")
     */
    public function resetBruteForceAction(Request $request)
    {
        $this->securityChecks();

        $this->userProvider->resetUsernameAttempts(self::BEHAT_EMAIL);

        return new Response(self::BEHAT_EMAIL . " attempts reset done");
    }

    /**
     * @Route("/document-list/{orderIdentifier}")
     */
    public function orderDocumentsList(Request $request, $orderIdentifier)
    {
        $this->securityChecks();

        $ret = [];

        // get Order for behat case
        $order = $this->getOrderFromIdentifier($orderIdentifier);

        $documents = $order->getDocuments();
        foreach ($documents as $document) {
            $ret[] = $document->getRemoteStorageReference();
        }

        return new Response(implode("|", array_filter($ret)));
    }



    private function getOrderFromIdentifier($orderIdentifier)
    {
        list($caseNumber, $orderType) = explode('-', $orderIdentifier);

        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => self::BEHAT_CASE_NUMBER]);

        $repo = $orderType == 'PF' ? OrderPf::class : OrderHw::class;

        return $this->em->getRepository($repo)->findOneBy(['client' => $client->getId()]);
    }
}
