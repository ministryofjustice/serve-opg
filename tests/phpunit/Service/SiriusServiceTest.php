<?php

namespace AppBundle\Service;

use AppBundle\Entity\Document;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPf;
use AppBundle\Service\File\Storage\S3Storage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SiriusServiceTest extends MockeryTestCase
{
    /**
     * @var SiriusService
     */
    protected $sut;

    private $mockEntityManager;
    private $mockClient;
    private $mockS3Storage;

    public function setUp()
    {
        $this->mockEntityManager = m::mock(EntityManager::class);
        $this->mockClient = m::mock(ClientInterface::class);
        $this->mockS3Storage = m::mock(S3Storage::class);

        $this->sut = new SiriusService($this->mockEntityManager, $this->mockClient, $this->mockS3Storage);
    }

    public function testServeOrder()
    {
        $mockOrder = m::mock(OrderPf::class)->makePartial();

        $mockDocument = m::mock(Document::class);
        $documents = new ArrayCollection([$mockDocument]);
        $mockOrder->setDocuments($documents);

        $this->mockS3Storage->shouldReceive('moveDocuments')
            ->once()
            ->with($mockOrder->getDocuments())
            ->andReturn($documents);

        $this->mockEntityManager->shouldReceive('persist')->times(count($documents));
        $this->mockEntityManager->shouldReceive('flush')->once();

        $this->sut->serveOrder($mockOrder);

    }
}
