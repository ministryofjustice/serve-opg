<?php declare(strict_types=1);


namespace AppBundle\Repository;


use Doctrine\ORM\EntityManagerInterface;

class DocumentRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function findById()
    {

    }
}