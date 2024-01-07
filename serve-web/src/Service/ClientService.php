<?php

namespace App\Service;

use App\Entity\Client;
use Doctrine\ORM\EntityManager;

class ClientService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function upsert(string $caseNumber, string $clientName): Client
    {
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $caseNumber]);
        if (!$client) {
            $client = new Client($caseNumber, $clientName, new \DateTime());
            $this->em->persist($client);
            $this->em->flush();
        }

        return $client;
    }
}
