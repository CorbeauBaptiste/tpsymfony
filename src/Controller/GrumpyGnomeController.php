<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GrumpyGnomeController extends AbstractController
{
    #[Route('/grumpy/gnome', name: 'app_grumpy_gnome')]
    public function index(): Response
    {
        return $this->render('grumpygnome/index.html.twig', [
            'controller_name' => 'GrumpyGnomeController',
        ]);
    }
}