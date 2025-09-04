<?php

declare(strict_types=1);

use App\Entity\Client;
use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
    private function deleteClient(int $clientId): void
    {
        $clientRepo = $this->_em->getRepository(Client::class);
        $client = $clientRepo->findOneBy(['id' => $clientId]);

        if ($client) {
            $this->_em->remove($client);
            $this->_em->flush();
        }
    }
}
