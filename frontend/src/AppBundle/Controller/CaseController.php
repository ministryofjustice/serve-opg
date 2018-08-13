<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/case")
 */
class CaseController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserController constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("", name="case-list")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Case:index.html.twig', [
            'clients' => $this->em->getRepository(Client::class)->findAll()
        ]);
    }

}