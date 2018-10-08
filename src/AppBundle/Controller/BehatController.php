<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPf;
use AppBundle\Entity\User;
use AppBundle\Service\ClientService;
use AppBundle\Service\OrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * BehatController constructor.
     * @param EntityManager $em
     * @param ClientService $clientService
     * @param OrderService $orderService
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManager $em, ClientService $clientService, OrderService $orderService, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->clientService = $clientService;
        $this->orderService = $orderService;
        $this->encoder = $encoder;
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
    public function userReset(Request $request)
    {
        $this->securityChecks();

        // add user if not existing
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::BEHAT_EMAIL]);
        if ($user) {
            $ret = "User " . self::BEHAT_EMAIL . " already present";
        } else {
            $user = new User(self::BEHAT_EMAIL);
            $encodedPassword = $this->encoder->encodePassword($user, self::BEHAT_PASSWORD);
            $user->setPassword($encodedPassword);
            $this->em->persist($user);
            $this->em->flush($user);
            $ret = "User " . self::BEHAT_EMAIL . " created";
        }

        return new Response($ret);
    }

    /**
     * //TODO protect from running on production ?
     *
     * @Route("/reset-behat-orders")
     */
    public function indexAction(Request $request)
    {
        $this->securityChecks();

        $ret = [];

        // empty orders for behat client
        $client  = $this->em->getRepository(Client::class)->findBy(['caseNumber'=>self::BEHAT_CASE_NUMBER]);
        $clientOrders = $this->em->getRepository(Order::class)->findBy(['client' => $client]);
        foreach($clientOrders as $order) {
            $this->orderService->emptyOrder($order);
            $ret[] = get_class($order). " for client " . self::BEHAT_CASE_NUMBER . " present and emptied (docs, deputies)";
        }

        return new Response(implode("\n", array_filter($ret)));
    }

    /**
     * //TODO protect from running on production ?
     *
     * @Route("/document-list/{orderIdentifier}")
     */
    public function orderDocumentsList(Request $request, $orderIdentifier)
    {
        $this->securityChecks();

        $ret = [];

        // get Order for behat case
        $order = $this->getOrderFromIdentifier($orderIdentifier);

        $documents = $order->getDocuments();
        foreach($documents as $document) {
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
