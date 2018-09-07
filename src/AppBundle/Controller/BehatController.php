<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderHw;
use AppBundle\Entity\OrderPa;
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
        // add user if not existing
        $user = $this->em->getRepository(User::class)->findOneBy(['email'=>self::BEHAT_EMAIL]);
        if (!$user) {
            $user = new User(self::BEHAT_EMAIL);
            $pass = $this->encoder->encodePassword($user, $user['password']);
            $user->setPassword($pass);
            $this->em->persist($user);
        }

        //add client if not existing
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber'=>self::BEHAT_CASE_NUMBER]);
        if (!$client) {
            $client = new Client(self::BEHAT_CASE_NUMBER,'Behat User', new \DateTime());
            $this->em->persist($client);
        }

        // add orders if not existing. If existing, reset them (3 quetions to null, and no deputies)
        foreach([OrderPa::class, OrderHw::class] as $orderClass) {
            $order = $this->em->getRepository($orderClass)->findOneBy(['client' => $client]);
            if ($order) {
                foreach ($order->getDeputies() as $deputy) {
                    $this->em->remove($deputy);
                }
            } else {
                $order = new $orderClass($client);
                $this->em->persist($order);
            }
            $order
                ->setServedAt(null)
                ->setSubType(null)
                ->setHasAssetsAboveThreshold(null)
                ->setAppointmentType(null);
        }

        $this->em->flush();

        return new JsonResponse(true);
    }

}