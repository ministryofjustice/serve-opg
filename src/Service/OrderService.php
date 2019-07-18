<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Order;
use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderService
{
    const APPOINTMENT_TYPE_SUB_TYPE_REGEX = <<<REGEX
/ORDER\s*APPOINTING\s*(?:A|AN|)\s*(NEW|INTERIM|)\s*(?:JOINT\s*AND\s*|)(SEVERAL|JOINT|)\s*(?:DEPUTIES|DEPUTY)/m
REGEX;

    const CASE_NUMBER_REGEX = '/No\. ([A-Z0-9]*)/m';
    const BOND_REGEX = '/sum of (.*?) in/';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SiriusService
     */
    private $siriusService;

    /**
     * @var DocumentReaderService
     */
    private $documentReader;

    /**
     * OrderService constructor.
     * @param EntityManager $em
     * @param SiriusService $siriusService
     * @param DocumentReaderService $documentReader
     */
    public function __construct(
        EntityManager $em,
        SiriusService $siriusService,
        DocumentReaderService $documentReader
    )
    {
        $this->em = $em;
        $this->siriusService = $siriusService;
        $this->documentReader = $documentReader;
    }

    public function serve(Order $order)
    {
        if (!$order->readyToServe()) {
            throw new \RuntimeException("Order not ready to be served");
        }

        // Make API call to Sirius
        try {
            $this->siriusService->serveOrder($order);

            $order->setServedAt(new \DateTime());
            $this->em->persist($order);
            $this->em->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param int $orderId
     * @return Order
     */
    public function getOrderByIdIfNotServed(int $orderId)
    {
        /** @var $order Order */
        $order = $this->em->getRepository(Order::class)->find($orderId);

        if (!$order) {
            throw new \RuntimeException("Order not existing");
        }
        if ($order->getServedAt()) {
            throw new AccessDeniedException('Cannot access an already served order');
        }

        return $order;
    }

    /**
     * @param Client $client
     * @param string $orderClass
     * @param \DateTime $issuedAt
     *
     * @return Order
     */
    public function upsert(Client $client, string $orderClass, \DateTime $madeAt, \DateTime $issuedAt)
    {
        /* @var $order Order */
        $order = $this->em->getRepository($orderClass)->findOneBy(['client' => $client]);
        if (!$order) {
            $order = new $orderClass($client, $madeAt, $issuedAt);
            $this->em->persist($order);
            $this->em->flush($client);
        }

        return $order;
    }

    /**
     * @param Order $order
     */
    public function emptyOrder(Order $order)
    {
        $orderId = $order->getId();
        $this->em->clear();
        $order = $this->em->getRepository(Order::class)->find($orderId);

        foreach ($order->getDeputies() as $deputy) {
            $this->em->remove($deputy);
        }
        foreach ($order->getDocuments() as $document) {
            $this->em->remove($document);
        }

        $order
            ->setServedAt(null)
            ->setSubType(null)
            ->setHasAssetsAboveThreshold(null)
            ->setAppointmentType(null);

        $this->em->flush();
    }

    /**
     * @param UploadedFile $file
     * @param Order $dehydratedOrder
     * @return Order
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function hydrateOrderFromDocument(UploadedFile $file, Order $dehydratedOrder)
    {
        // @todo catch exceptions for:
        //     - unknown mime type
        //     - unreadable file

        $orderText = $this->documentReader->readWordDoc($file->getRealPath());

        // @todo catch errors:
        //    - Case number doesn't match
        //    - Couldn't extract case number

        $hydratedOrder = $this->answerQuestionsFromText($orderText, $dehydratedOrder);

        return $hydratedOrder;
    }

    /**
     * @param string $fileContents, Text extracted from Court Order
     * @param Order $order
     *
     * @return Order Returns an updated version of the order
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function answerQuestionsFromText(string $fileContents, Order $order)
    {
        if (!$this->extractCaseNumber($fileContents, $order)) {
            throw new WrongCaseNumberException(
                'The case number in the document does not match the case number for this order. Please check the file and try again.'
            );
        }

        // Answer the questions from the order
        $this->extractAppointmentTypeAndSubtype($fileContents, $order);

        if ($order->getType() === $order::TYPE_PF || $order->getType() === $order::TYPE_BOTH) {
            $this->extractBondType($fileContents, $order);
        }

        return $order;
    }

    /**
     * @param string $text
     * @param Order $order
     * @return bool
     */
    private function extractCaseNumber(string $text, Order $order)
    {
        preg_match(self::CASE_NUMBER_REGEX, $text, $matches);

        if ($matches[1] === $order->getClient()->getCaseNumber()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $text
     * @param string $regex
     * @param Order $order
     * @throws NoMatchesFoundException
     */
    private function extractAppointmentTypeAndSubtype(string $text, Order $order)
    {
        preg_match(self::APPOINTMENT_TYPE_SUB_TYPE_REGEX, $text, $matches);

        if (empty($matches)) {
            return;
        }

        switch ($matches[1]) {
            case null:
                $order->setSubType(Order::SUBTYPE_NEW);
                break;
            case 'NEW':
                $order->setSubType(Order::SUBTYPE_REPLACEMENT);
                break;
            case 'INTERIM':
                $order->setSubType(Order::SUBTYPE_INTERIM_ORDER);
                break;
            default:
                $order->setSubType(null);
        }

        switch ($matches[2]) {
            case null:
                $order->setAppointmentType(Order::APPOINTMENT_TYPE_SOLE);
                break;
            case 'SEVERAL':
                $order->setAppointmentType(Order::APPOINTMENT_TYPE_JOINT_AND_SEVERAL);
                break;
            case 'JOINT':
                $order->setAppointmentType(Order::APPOINTMENT_TYPE_JOINT);
                break;
            default:
                $order->setAppointmentType(null);
        }
    }

    /**
     * @param string $text
     * @param Order $order
     */
    private function extractBondType(string $text, Order $order)
    {
        preg_match(self::BOND_REGEX, $text, $matches);

        if (empty($matches)) {
            return;
        }

        $bond = preg_replace("/[^a-zA-Z0-9]/", "", $matches[1]);

        if (empty($bond)) {
            return;
        }

        if ($bond >= 21000) {
            $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_YES);
        } elseif ($bond < 21000) {
            $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_NO);
        }
    }
}
