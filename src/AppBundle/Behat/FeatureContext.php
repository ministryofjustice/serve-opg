<?php

namespace AppBundle\Behat;

use AppBundle\Entity\Client;
use AppBundle\Entity\Order;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Behat context class.
 */
class FeatureContext extends MinkContext implements KernelAwareContext
{
    use RegionTrait;
    use FormTrait;

    /**
     * @var EntityManager
     */
    private $em;

    public function setKernel(KernelInterface $kernel)
    {
        $this->em = $kernel->getContainer()->get(EntityManager::class);
    }



}
