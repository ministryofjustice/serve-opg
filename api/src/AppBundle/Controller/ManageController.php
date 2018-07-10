<?php

namespace AppBundle\Controller;

use Aws\DynamoDb\DynamoDbClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/manage")
 */
class ManageController extends Controller
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        $errors = [];

        if ($errors) {
            return new Response(implode('<br/>', $errors), 500);
        }

        return new Response('OK', 200);
    }


    /**
     * @Route("/elb", name="manage-elb")
     * @Method({"GET"})
     * @Template()
     */
    public function elbAction()
    {
        return ['status' => 'OK'];
    }

}
