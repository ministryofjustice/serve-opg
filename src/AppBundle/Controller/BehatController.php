<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPf;
use AppBundle\Entity\User;
use AppBundle\Service\ClientService;
use AppBundle\Service\MailSender;
use AppBundle\Service\OrderService;
use AppBundle\Service\Security\LoginAttempts\UserProvider;
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
     * @var MailSender
     */
    private $mailerSender;

    /**
     * BehatController constructor.
     * @param EntityManager $em
     * @param ClientService $clientService
     * @param OrderService $orderService
     * @param UserPasswordEncoderInterface $encoder
     * @param UserProvider $userProvider
     */
    public function __construct(EntityManager $em, ClientService $clientService, OrderService $orderService, UserPasswordEncoderInterface $encoder, UserProvider $userProvider, MailSender $mailerSender)
    {
        $this->em = $em;
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->encoder = $encoder;
        $this->userProvider = $userProvider;
        $this->mailerSender = $mailerSender;
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
            $ret = "User " . self::BEHAT_EMAIL . " already present, password reset";
        }

        $encodedPassword = $this->encoder->encodePassword($user, self::BEHAT_PASSWORD);
        $user->setPassword($encodedPassword);

        $this->em->flush($user);
        $ret = "User " . self::BEHAT_EMAIL . " created";

        return new Response($ret);
    }

    /**
     * @Route("/reset-behat-orders")
     */
    public function resetBehatOrdersAction(Request $request)
    {
        $this->securityChecks();

        $ret = [];

        // empty orders for behat client
        $client = $this->em->getRepository(Client::class)->findBy(['caseNumber' => self::BEHAT_CASE_NUMBER]);
        $clientOrders = $this->em->getRepository(Order::class)->findBy(['client' => $client]);
        foreach ($clientOrders as $order) {
            $this->orderService->emptyOrder($order);
            $ret[] = get_class($order) . " for client " . self::BEHAT_CASE_NUMBER . " present and emptied (docs, deputies)";
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

    /**
     * @Route("/open-link-in-last-email")
     */
    public function openLinkInLastEmail()
    {
        $this->securityChecks();

        $notificationId = 'TODO. take from DB ?';

        // ping notify for the last email being sent
        $attempts = 0;
        do {
            sleep(1);
            $status = $this->mailerSender->getLastEmailStatus($notificationId);
        } while ($status != 'delivered' && $attempts++ < 5);

        if ($status != 'delivered') {
            throw new \RuntimeException("Email failed to deliver after $attempts attempts");
        }

        preg_match('#https?://[\/\w-]+#', $status['body'], $links);
        if (empty($links)) {
            throw new \RuntimeException("No link found in the email");
        }

        return new RedirectResponse($links[0]);

    }

    private function getOrderFromIdentifier($orderIdentifier)
    {
        list($caseNumber, $orderType) = explode('-', $orderIdentifier);

        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => self::BEHAT_CASE_NUMBER]);

        $repo = $orderType == 'PF' ? OrderPf::class : OrderHw::class;

        return $this->em->getRepository($repo)->findOneBy(['client' => $client->getId()]);
    }
}
