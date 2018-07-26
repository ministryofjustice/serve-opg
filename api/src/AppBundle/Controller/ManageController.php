<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
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

        if (!$this->get('em')->getRepository(User::class)->findBy([], [], 1)) {
            $errors [] = 'Users table not found';
        }
        if ($errors) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        return [];
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
