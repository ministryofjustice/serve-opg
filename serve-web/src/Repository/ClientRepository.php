<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Client;
use Doctrine\ORM\EntityRepository;

class ClientRepository extends EntityRepository
{
    public function delete(int $clientId): void
    {
        $clientRepo = $this->getEntityManager()->getRepository(Client::class);
        $client = $clientRepo->findOneBy(['id' => $clientId]);

        if (!is_null($client)) {
            $this->getEntityManager()->remove($client);
            $this->getEntityManager()->flush();
        }
    }
}
