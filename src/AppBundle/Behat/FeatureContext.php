<?php

namespace AppBundle\Behat;

use AppBundle\Entity\Client;
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

    /**
     * @var EntityManager
     */
    private $em;

    public function setKernel(KernelInterface $kernel)
    {
        $this->em = $kernel->getContainer()->get(EntityManager::class);
    }

    /**
     * @Given The case :caseNumber orders are empty
     */
    public function emptyClientOrders($caseNumber)
    {
        $client = $this->em->getRepository(Client::class)->findOneBy(['caseNumber'=>$caseNumber]);

        foreach($client->getOrders() as $order) {
            $order->setSubType(null)->setHasAssetsAboveThreshold(null);
            foreach($order->getDeputys() as $deputy) {
               $this->em->remove($deputy);
            }
        }
        $this->em->flush();
    }


}
