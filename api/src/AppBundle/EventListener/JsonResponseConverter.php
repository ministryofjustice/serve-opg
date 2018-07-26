<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class JsonResponseConverter
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }


    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $data = [
            'success' => true,
            'data' => $event->getControllerResult(),
        ];

        $serializedData = $this->serializer->serialize($data, 'json');
        $response = new Response($serializedData, 200, ['Content-Type' => 'application/json']);
        $event->setResponse($response);
    }


    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        $data = [
            'success' => false,
            'exception' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => substr($e->getTraceAsString(), 0, 10000)
            ],
            'message' => '',
        ];

        $serializedData = $this->serializer->serialize($data, 'json');
        $response = new Response($serializedData, 500, ['Content-Type' => 'application/json']);
        $event->setResponse($response);
    }

}
