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

    public function ping()
    {
        try {
            $this->em->getConnection()->query('select * from information_schema.tables LIMIT 1')->fetchAll();

            $this->isHealthy = true;
            $this->errors = "";
         } catch (\Throwable $e) {
            // customise error message if possible
            echo($e->getMessage());
             $returnMessage = 'Database generic error';
            if ($e instanceof \PDOException && 7 === $e->getCode()) {
                $returnMessage = 'Database service not reachable ('.$e->getMessage().')';
            }

            $this->isHealthy = false;
            $this->errors = $returnMessage;
        }
    }

    public function getName()
    {
        return 'Database';
    }
}
