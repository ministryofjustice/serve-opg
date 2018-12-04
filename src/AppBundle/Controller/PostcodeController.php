<?php
/**
 * Project: opg-digicop
 * Author: robertford
 * Date: 04/12/2018
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PostcodeController  extends Controller
{

    /**
     * @var OrdnanceSurvey
     */
    private $addressLookup;

    public function indexAction()
    {
        $postcode = $this->params()->fromQuery('postcode');
        if (empty($postcode)) {
            return $this->notFoundAction();
        }
        $addresses = [];
        try {
            $addresses = $this->addressLookup->lookupPostcode($postcode);
        }catch (\RuntimeException $e) {
            $this->getLogger()->warn("Exception from postcode lookup: ".$e->getMessage());
        }
        return new JsonModel([
            'isPostcodeValid' => true,
            'success'         => (count($addresses) > 0),
            'addresses'       => $addresses,
        ]);
    }
    public function setAddressLookup(OrdnanceSurvey $addressLookup)
    {
        $this->addressLookup = $addressLookup;
    }

}
