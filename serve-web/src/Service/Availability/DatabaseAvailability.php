<?php

namespace App\Service\Availability;

use Doctrine\ORM\EntityManager;

class DatabaseAvailability extends ServiceAvailabilityAbstract
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function ping(): void
    {
        try {
            $this->em->getConnection()->executeQuery('select * from information_schema.tables LIMIT 1')->rowCount();
            $this->isHealthy = true;
            $this->errors = '';
        } catch (\Throwable $e) {
            // customise error message if possible
            echo $e->getMessage();
            $returnMessage = 'Database generic error';
            if ($e instanceof \PDOException && 7 === $e->getCode()) {
                $returnMessage = 'Database service not reachable ('.$e->getMessage().')';
            }

            $this->isHealthy = false;
            $this->errors = $returnMessage;
        }
    }

    public function getName(): string
    {
        return 'Database';
    }
}
