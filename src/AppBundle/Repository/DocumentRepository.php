<?php declare(strict_types=1);


namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DocumentRepository extends EntityRepository
{
    /**
     * @param int $id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findById(int $id)
    {
        return $this->getEntityManager()->createQuery('SELECT d FROM AppBundle:Document d WHERE d.id = :id')
            ->setParameter('id', $id)
            ->getSingleResult();
    }
}