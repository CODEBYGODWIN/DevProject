<?php

namespace App\Controller;

use App\Document\User; // Notez qu'on utilise Document et non Entity
use Doctrine\ODM\MongoDB\DocumentManager; // DocumentManager pour MongoDB
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfileAnotherUserController extends AbstractController
{
    /**
     * @Route("/profile/{id}", name="profile_view")
     */
    public function view($id, DocumentManager $documentManager): Response
    {
        $user = $documentManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
        
        return $this->render('profile/view.html.twig', [
            'user' => $user,
        ]);
    }
}