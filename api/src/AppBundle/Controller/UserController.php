<?php

namespace AppBundle\Controller;

use Common\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/all", name="user_all")
     */
    public function all()
    {
        return $this->get('em')->getRepository(User::class)->findAll();
    }

    /**
     * @Route("/by-id/{id}", name="user_by_id", requirements={"id":"\d+"})
     */
    public function oneById($id)
    {
        return $this->get('em')->getRepository(User::class)->findOneBy(['id'=>$id]);
    }

    /**
     * @Route("/by-email/{email}", name="user_by_email", requirements={"email":"[\w@.]+"})
     */
    public function oneByEmail($email)
    {
        return $this->get('em')->getRepository(User::class)->findOneBy(['email'=>$email]);
    }
}