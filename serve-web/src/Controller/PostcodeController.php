<?php

namespace App\Controller;

use App\Service\AddressLookup\OrdnanceSurvey;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PostcodeController extends AbstractController
{
    private LoggerInterface $logger;

    /**
     * PostcodeController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/postcode-lookup", name="postcode-lookup")
     */
    public function postcodeLookupAction(Request $request, OrdnanceSurvey $ordnanceSurvey): JsonResponse
    {
        $postcode = $request->query->get('postcode');

        if (empty($postcode)) {
            return new JsonResponse([ 'error'=> 'Please provide a postcode.']);
        }
        $addresses = [];
        try {
            $addresses = $ordnanceSurvey->lookupPostcode($postcode);
        }catch (\Exception $e) {
            $this->logger->error("Exception from postcode lookup: ". $e->getMessage());
            return new JsonResponse([ 'error'=> $e->getMessage()]);
        }

        return new JsonResponse([
            'isPostcodeValid' => true,
            'success'         => (count($addresses) > 0),
            'addresses'       => $addresses,
        ]);
    }

}
