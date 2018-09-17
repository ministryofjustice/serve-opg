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
    // keep in sync with behat-users.csv
    const BEHAT_CASE_NUMBER = '12345678';

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
     * //TODO protect from running on production ?
     *
     * @Route("/behat-user-upsert")
     */
    public function userReset(Request $request)
    {
        $ret = [];

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
        $ret = [];

        // empty orders for behat client
        $client  = $this->em->getRepository(Client::class)->findBy(['caseNumber'=>self::BEHAT_CASE_NUMBER]);
        foreach($this->em->getRepository(Order::class)->findBy(['client' => $client]) as $order) {
            $this->orderService->emptyOrder($order);
            $ret[] = get_class($order). " for client " . self::BEHAT_CASE_NUMBER . " present and emptied (docs, deputies)";
        }

        return new Response(implode("\n", array_filter($ret)));
    }

}