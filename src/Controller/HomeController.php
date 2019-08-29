<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="Home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }


    /**
     * @Route("/", name="wrong_pass")
     */
    public function wrongPass()
    {
        return $this->render('security/forgotten_password.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }
}
