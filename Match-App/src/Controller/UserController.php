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
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
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
                $uploadsDirectory = $this->getParameter('uploads_directory'); // Défini dans services.yaml
                $newFilename = uniqid().'.'.$file->guessExtension();

                try {
                    // Supprime l'ancienne image si elle existe
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

            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            $dm->persist($user);
            $dm->flush();
            
            $this->addFlash('success', 'Votre compte a été créé avec succès !');
            return $this->redirectToRoute('login');
        }
        
        return $this->render('registration/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}