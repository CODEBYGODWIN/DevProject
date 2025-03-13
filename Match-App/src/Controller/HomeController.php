<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        
        $user = $session->get('user');
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        return $this->render('home/home.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/', name: 'default_home')]
    public function defaultHome(): Response
    {
        return $this->redirectToRoute('registration');
    }

}
