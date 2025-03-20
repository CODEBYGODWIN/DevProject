<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UserController extends AbstractController
{
    #[Route('/registration', name: 'registration', methods: ['GET', 'POST'])]
    public function index(
        Request $request, 
        DocumentManager $dm,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();
        
        $form = $this->createForm(UserType::class, $user);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $existingUser = $dm->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->redirectToRoute('registration');
            }
            
            $file = $form->get('picture')->getData();

            if ($file) {
                $uploadsDirectory = $this->getParameter('uploads_directory');
                $newFilename = uniqid().'.'.$file->guessExtension();

                try {
                    $file->move($uploadsDirectory, $newFilename);
                    $user->setPicture($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload du fichier.');
                }
            }
            
            
            $idCardFile = $form->get('idCard')->getData();
            if ($idCardFile) {
                $idCardDirectory = $this->getParameter('uploads_directory') . '/id_cards';
                
                
                if (!file_exists($idCardDirectory)) {
                    mkdir($idCardDirectory, 0755, true);
                }
                
                $newIdCardFilename = 'id_card_' . uniqid() . '.' . $idCardFile->guessExtension();
                
                try {
                    $idCardFile->move($idCardDirectory, $newIdCardFilename);
                    $user->setIdCard($newIdCardFilename);
                    $user->setIdCardVerified(false); 
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de votre carte d\'identité.');
                }
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            $dm->persist($user);
            $dm->flush();
            
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Veuillez vous connecter.');
            
            return $this->redirectToRoute('login');
        }
        
        return $this->render('registration/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}