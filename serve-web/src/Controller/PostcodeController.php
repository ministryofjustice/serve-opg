<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AddressLookup\OrdnanceSurvey;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostcodeController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/postcode-lookup', name: 'postcode-lookup')]
    public function postcodeLookupAction(Request $request, OrdnanceSurvey $ordnanceSurvey): JsonResponse
    {
        $postcode = $request->query->get('postcode');

        if (empty($postcode)) {
            return new JsonResponse(['error' => 'Please provide a postcode.']);
        }

        try {
            $addresses = $ordnanceSurvey->lookupPostcode($postcode);
        } catch (\Exception $e) {
            $this->logger->error('Exception from postcode lookup: '.$e->getMessage());

            return new JsonResponse(['error' => $e->getMessage()]);
        }

        return new JsonResponse([
            'isPostcodeValid' => true,
            'success' => (count($addresses) > 0),
            'addresses' => $addresses,
        ]);
    }
}
