<?php

namespace App\Controller;

use App\Document\User;
use App\Form\UserType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function showProfile(SessionInterface $session, DocumentManager $dm): Response
    {
        $userData = $session->get('user');

        if (!$userData) {
            return $this->redirectToRoute('login');
        }

        $user = $dm->getRepository(User::class)->find($userData['id']);

        return $this->render('Profile/profile.html.twig', [
            'user' => $user,
        ]);
    }

    // #[Route('/profile/edit', name: 'profile_edit')]
    // public function editProfile(Request $request, SessionInterface $session, DocumentManager $dm): Response
    // {
    //     $userData = $session->get('user');

    //     if (!$userData) {
    //         return $this->redirectToRoute('login');
    //     }

    //     $user = $dm->getRepository(User::class)->find($userData['id']);

    //     $form = $this->createForm(UserType::class, $user);
    //     $form->remove('password'); 
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $dm->flush();
    //         $session->set('user', [
    //             'id' => $user->getId(),
    //             'email' => $user->getEmail(),
    //             'username' => $user->getUsername(),
    //         ]);

    //         $this->addFlash('success', 'Profil mis à jour avec succès.');
    //         return $this->redirectToRoute('profile');
    //     }

    //     return $this->render('profile/edit.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }
}
