<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPf;
use AppBundle\Entity\User;
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
    const BEHAT_CASE_NUMBER = '12345678';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * BehatController constructor.
     * @param EntityManager $em
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManager $em, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->encoder = $encoder;
    }

    /**
     * //TODO protect from running on production ?
     *
     * @Route("/fixture-reset")
     */
    public function indexAction(Request $request)
    {
        $ret = [];

        // add user if not existing
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::BEHAT_EMAIL]);
        if ($user) {
            $ret[] = "User " . self::BEHAT_EMAIL . " already present";
        } else {
            $user = new User(self::BEHAT_EMAIL);
            $encodedPassword = $this->encoder->encodePassword($user, self::BEHAT_PASSWORD);
            $user->setPassword($encodedPassword);
            $this->em->persist($user);
            $ret[] = "User " . self::BEHAT_EMAIL . " created";
        }

        // add client if not existing
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => self::BEHAT_CASE_NUMBER]);
        if ($client) {
            $ret[] = "Client " . self::BEHAT_CASE_NUMBER . " already created";
        } else {
            $client = new Client(self::BEHAT_CASE_NUMBER, 'Behat User', new \DateTime());
            $this->em->persist($client);
            $ret[] = "Client " . self::BEHAT_CASE_NUMBER . " created";
        }

        // add orders if not existing. If existing, reset them (3 quetions to null, and no deputies)
        foreach ([OrderPf::class, OrderHw::class] as $orderClass) {
            $order = $this->em->getRepository($orderClass)->findOneBy(['client' => $client]); /* @var $order Order */
            if ($order) {
                foreach ($order->getDeputies() as $deputy) {
                    $this->em->remove($deputy);
                }
                foreach ($order->getDocuments() as $document) {
                    $this->em->remove($document);
                }
                $ret[] = $orderClass . " for client " . self::BEHAT_CASE_NUMBER . " already created, deputies and documents dropped";
            } else {
                $order = new $orderClass($client, new \DateTime('3 days ago'));
                $this->em->persist($order);
                $ret[] = $orderClass . " for client " . self::BEHAT_CASE_NUMBER . " created";
            }
            $order
                ->setServedAt(null)
                ->setSubType(null)
                ->setHasAssetsAboveThreshold(null)
                ->setAppointmentType(null);
        }

        $this->em->flush();

        return new Response(implode("\n", array_filter($ret)));
    }

}