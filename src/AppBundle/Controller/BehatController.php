<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/behat/")
 */
class BehatController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * //TODO protect from running on production ?
     *
     * @Route("case/empty/{caseNumber}/{type}")
     */
    public function indexAction(Request $request, $caseNumber, $type)
    {
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber'=>$caseNumber]);

        foreach($client->getOrders() as $order) { /* @var $order Order */
            if($order->getType() == $type) {
                $order->setSubType(null)
                    ->setHasAssetsAboveThreshold(null)
                    ->setAppointmentType(null);
                foreach($order->getDeputies() as $deputy) {
                    $this->em->remove($deputy);
                }
            }
        }
        $this->em->flush();

        return new JsonResponse(true);
    }

}