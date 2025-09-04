<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Order;
use App\exceptions\NoMatchesFoundException;
use App\exceptions\WrongCaseNumberException;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderService
{
    public const APPOINTMENT_TYPE_SUB_TYPE_REGEX = <<<REGEX
/ORDER\s*APPOINTING\s*(?:A|AN|)\s*(NEW|INTERIM|)\s*(?:JOINT\s*AND\s*|)(SEVERAL|JOINT|)\s*(?:DEPUTIES|DEPUTY)/m
REGEX;

    public const CASE_NUMBER_REGEX = '/No\. ([A-Z0-9]*)/m';
    public const BOND_REGEX = '/sum of (.*) in/';

    private readonly OrderRepository|EntityRepository $orderRepository;

    public function __construct(
        private readonly EntityManager $em,
        private readonly SiriusService $siriusService,
        private readonly LoggerInterface $logger,
    ) {
        $this->orderRepository = $this->em->getRepository(Order::class);
    }

    public function isAvailable(): bool
    {
        return $this->siriusService->ping();
    }

    public function serve(Order $order): void
    {
        if (!$order->readyToServe()) {
            throw new \RuntimeException('Order not ready to be served');
        }

        if (!$this->isAvailable()) {
            throw new \RuntimeException('Sirius is currently unavilable');
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

    public function getOrderByIdIfNotServed(int $orderId): Order
    {
        /** @var $order Order */
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            throw new \RuntimeException('Order not existing');
        }
        if ($order->getServedAt()) {
            throw new AccessDeniedException('Cannot access an already served order');
        }

        return $order;
    }

    public function upsert(
        Client $client,
        string $orderClass,
        \DateTime $madeAt,
        \DateTime $issuedAt,
        string $orderNumber
    ): Order {
        /* @var $order Order */
        $order = $this->em->getRepository($orderClass)->findOneBy([
            'client' => $client,
            'orderNumber' => $orderNumber,
        ]);

        if (!$order) {
            // Create a new order if no matching order is found
            $order = new $orderClass($client, $madeAt, $issuedAt, $orderNumber);
            $this->em->persist($order);
            $this->em->flush();
        }

        return $order;
    }

    public function emptyOrder(Order $order): void
    {
        $orderId = $order->getId();
        $this->em->clear();
        $order = $this->orderRepository->find($orderId);

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
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
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
     * @throws NoMatchesFoundException
     * @throws WrongCaseNumberException
     */
    public function answerQuestionsFromText(string $fileContents, Order $order): Order
    {
        if (!$this->extractCaseNumber($fileContents, $order)) {
            throw new WrongCaseNumberException('The case number in the document does not match the case number for this order. Please check the file and try again.');
        }

        // Answer the questions from the order
        $this->extractAppointmentTypeAndSubtype($fileContents, $order);

        if ($order->getType() === $order::TYPE_PF || $order->getType() === $order::TYPE_BOTH) {
            $this->extractBondType($fileContents, $order);
        }

        return $order;
    }

    public function deletionByOrderId(int $orderId): void
    {
        try {
            $this->orderRepository->delete($orderId);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to delete order due to error: %s', $e->getMessage()));
        }
    }

    private function extractCaseNumber(string $text, Order $order): bool
    {
        preg_match(self::CASE_NUMBER_REGEX, $text, $matches);

        if ($matches[1] === $order->getClient()->getCaseNumber()) {
            return true;
        }

        return false;
    }

    private function extractAppointmentTypeAndSubtype(string $text, Order $order): void
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

    private function extractBondType(string $text, Order $order): void
    {
        preg_match(self::BOND_REGEX, $text, $matches);

        if (empty($matches)) {
            return;
        }

        $bond = preg_replace('/[^a-zA-Z0-9]/', '', $matches[1]);

        switch ($bond) {
            case '':
                $order->setHasAssetsAboveThreshold(null);
                break;
            case $bond >= 21000:
                $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_YES);
                break;
            case $bond < 21000:
                $order->setHasAssetsAboveThreshold(Order::HAS_ASSETS_ABOVE_THRESHOLD_NO);
                break;
        }
    }
}
