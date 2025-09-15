<?php

namespace App\Service;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class ClientService
{
    private readonly ClientRepository|EntityRepository $clientRepository;
    public function __construct(
        private readonly EntityManager $em,
        private readonly LoggerInterface $logger,
    ) {
        $this->clientRepository = $this->em->getRepository(Client::class);
    }

    public function upsert(string $caseNumber, string $clientName): Client
    {
        $client = $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);
        if (!$client) {
            $client = new Client($caseNumber, $clientName, new \DateTime());
            $this->em->persist($client);
            $this->em->flush();
        }

        return $client;
    }

    public function deletionByClientId(int $clientId): void
    {
        try {
            $this->clientRepository->delete($clientId);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to delete client due to error: %s', $e->getMessage()));
        }
    }

    public function findClientByCaseNumber(string $caseNumber): Client
    {
        return $this->clientRepository->findOneBy(['caseNumber' => $caseNumber]);
    }
}
