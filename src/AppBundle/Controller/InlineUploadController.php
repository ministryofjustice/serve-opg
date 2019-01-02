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

class InlineUploadController extends Controller
{
    /**
     * @Route("/upload-document", name="inline-upload")
     */
    public function uploadAction(Request $request, OrdnanceSurvey $ordnanceSurvey)
    {
//        $file =

        return new JsonResponse([
            'success'         => true
        ]);
    }

}
