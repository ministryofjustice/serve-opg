<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use Doctrine\ORM\EntityManager;

class ClientService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $caseNumber
     * @param $clientName
     * @return Client
     */
    public function upsert($caseNumber, $clientName)
    {
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $caseNumber]);
        if (!$client) {
            $client = new Client($caseNumber, $clientName, new \DateTime());
            $this->em->persist($client);
            $this->em->flush($client);
        }

        return $client;
    }

}