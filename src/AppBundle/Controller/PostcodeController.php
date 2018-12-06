<?php
/**
 * Project: opg-digicop
 * Author: robertford
 * Date: 04/12/2018
 */

namespace AppBundle\Controller;

use AppBundle\Service\AddressLookup\OrdnanceSurveyClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Service\AddressLookup\OrdnanceSurvey;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PostcodeController extends Controller
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

        return new JsonResponse([ 'test'=> $addresses]);
//        return new JsonResponse([
//            'isPostcodeValid' => true,
//            'success'         => (count($addresses) > 0),
//            'addresses'       => $addresses,
//        ]);
    }

}
