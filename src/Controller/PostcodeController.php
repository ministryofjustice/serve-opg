<?php

namespace App\Controller;

use App\Service\AddressLookup\OrdnanceSurveyClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Service\AddressLookup\OrdnanceSurvey;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PostcodeController extends AbstractController
{
    /**
     * @Route("/postcode-lookup", name="postcode-lookup")
     */
    public function postcodeLookupAction(Request $request, OrdnanceSurvey $ordnanceSurvey)
    {
        $postcode = $request->query->get('postcode');

        if (empty($postcode)) {
            return new JsonResponse([ 'error'=> 'Please provide a postcode.']);
        }
        $addresses = [];
        try {
            $addresses = $ordnanceSurvey->lookupPostcode($postcode);
        }catch (\Exception $e) {
            $this->get('logger')->error("Exception from postcode lookup: ". $e->getMessage());
            return new JsonResponse([ 'error'=> $e->getMessage()]);
        }

        return new JsonResponse([
            'isPostcodeValid' => true,
            'success'         => (count($addresses) > 0),
            'addresses'       => $addresses,
        ]);
    }

}
