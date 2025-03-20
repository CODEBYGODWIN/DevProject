<?php

namespace App\Controller;

use App\Document\User; 
use Doctrine\ODM\MongoDB\DocumentManager; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfileAnotherUserController extends AbstractController
{
    /**
     * @Route("/profile/{id}", name="profile_view")
     */
    public function view($id, DocumentManager $documentManager, Request $request): Response
    {
        $user = $documentManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
        
     
        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/view_popup.html.twig', [
                'user' => $user,
            ]);
        }
        
        // Redirection vers la page d'accueil pour les requêtes non-AJAX
        return $this->redirectToRoute('home');
    }
}