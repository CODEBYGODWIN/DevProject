<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserEditType; 
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileController extends AbstractController {
    #[Route('/profile', name: 'profile')]
    public function showProfile(Request $request, SessionInterface $session, DocumentManager $dm): Response
    {
      
        $userData = $session->get('user');
        
   
        if (!$userData) {
            return $this->redirectToRoute('login');
        }
        
        $user = $dm->getRepository(User::class)->find($userData['id']);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('login');
        }
        
        return $this->render('Profile/profile.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/profile/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(Request $request, SessionInterface $session, DocumentManager $dm): Response
    {
        $userData = $session->get('user');
        
        if (!$userData) {
            return $this->redirectToRoute('login');
        }
        
        $user = $dm->getRepository(User::class)->find($userData['id']);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('login');
        }
        
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('picture')->getData();
            
            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory'); 
                $newFilename = uniqid().'.'.$file->guessExtension();
                
                try {
                    
                    if ($user->getPicture()) {
                        $oldFile = $uploadsDirectory . '/' . $user->getPicture();
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                    
                    $file->move($uploadsDirectory, $newFilename);
                    $user->setPicture($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');
                }
            }
            
            $dm->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
            
            return $this->redirectToRoute('profile');
        }
        
        return $this->render('Profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/profile/id-verification', name: 'profile_id_verification')]
    public function idVerificationStatus(Request $request, SessionInterface $session, DocumentManager $dm): Response
    {
        $userData = $session->get('user');
        
        if (!$userData) {
            return $this->redirectToRoute('login');
        }
        
        $user = $dm->getRepository(User::class)->find($userData['id']);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('login');
        }
        
        return $this->render('profile/id_verification_status.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/profile/upload-id-card', name: 'profile_upload_id_card', methods: ['GET', 'POST'])]
    public function uploadIdCard(Request $request, SessionInterface $session, DocumentManager $dm): Response
    {
        $userData = $session->get('user');
        
        if (!$userData) {
            return $this->redirectToRoute('login');
        }
        
        $user = $dm->getRepository(User::class)->find($userData['id']);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('login');
        }
        
        if ($request->isMethod('POST')) {
            $idCardFile = $request->files->get('id_card');
            
            if ($idCardFile) {
                $idCardDirectory = $this->getParameter('uploads_directory') . '/id_cards';
                
               
                if (!file_exists($idCardDirectory)) {
                    mkdir($idCardDirectory, 0755, true);
                }
                
                
                if ($user->getIdCard()) {
                    $oldFile = $idCardDirectory . '/' . $user->getIdCard();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                $newIdCardFilename = 'id_card_' . uniqid() . '.' . $idCardFile->guessExtension();
                
                try {
                    $idCardFile->move($idCardDirectory, $newIdCardFilename);
                    $user->setIdCard($newIdCardFilename);
                    $user->setIdCardVerified(false);
                    $dm->flush();
                    
                    $this->addFlash('success', 'Votre carte d\'identité a été téléchargée avec succès. Elle sera vérifiée prochainement.');
                    return $this->redirectToRoute('profile_id_verification');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de votre carte d\'identité.');
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner un fichier.');
            }
        }
        
        return $this->render('profile/upload_id_card.html.twig', [
            'user' => $user,
        ]);
    }
}