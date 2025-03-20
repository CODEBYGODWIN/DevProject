<?php

namespace App\Controller;

use App\Document\User;
use App\Form\LoginType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class LoginController extends AbstractController {
    #[Route('/login', name: 'login')]
    public function login(Request $request, DocumentManager $dm, SessionInterface $session): Response
    {
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $dm->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            
            if (!$user || !password_verify($data['password'], $user->getPassword())) {
                $this->addFlash('error', 'Email ou mot de passe incorrect.');
                return $this->redirectToRoute('login');
            }
            
            
            if ($user->getIdCard() && !$user->isIdCardVerified()) {
                
                return $this->redirectToRoute('pending_verification');
            }
            
          
            if (!$user->getIdCard()) {
                $this->addFlash('warning', 'Vous devez télécharger votre carte d\'identité pour accéder à votre compte. Veuillez contacter l\'administrateur.');
                return $this->redirectToRoute('login');
            }
            
            
            $session->set('user', [
                'id' => $user->getId(),  
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ]);

           
            if (!$user->getInou()) {
                return $this->redirectToRoute('questionnaire');
            }

            return $this->redirectToRoute('home');
        }
        
        return $this->render('login/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/logout', name: 'logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('user');
        return $this->redirectToRoute('login');
    }
    
    #[Route('/pending-verification', name: 'pending_verification')]
    public function pendingVerification(): Response
    {
        return $this->render('login/pending_verification.html.twig');
    }
}