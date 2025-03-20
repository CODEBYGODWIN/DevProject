<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/id-verification', name: 'admin_id_verification')]
    public function idVerification(
        DocumentManager $dm
    ): Response
    {
        
        $users = $dm->getRepository(User::class)->findBy(['idCardVerified' => false, 'idCard' => ['$ne' => null]]);
        
        return $this->render('admin/id_verification.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/verify-id/{id}', name: 'admin_verify_id', methods: ['POST'])]
    public function verifyId(
        string $id,
        DocumentManager $dm,
        Request $request
    ): Response
    {
        $user = $dm->getRepository(User::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_id_verification');
        }
        
        $user->setIdCardVerified(true);
        $dm->flush();
        
        $this->addFlash('success', 'La carte d\'identité de ' . $user->getUsername() . ' a été vérifiée avec succès.');
        
        return $this->redirectToRoute('admin_id_verification');
    }
    
    #[Route('/reject-id/{id}', name: 'admin_reject_id', methods: ['POST'])]
    public function rejectId(
        string $id,
        DocumentManager $dm,
        Request $request
    ): Response
    {
        $user = $dm->getRepository(User::class)->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_id_verification');
        }
        
        
        $idCardPath = $this->getParameter('uploads_directory') . '/id_cards/' . $user->getIdCard();
        if (file_exists($idCardPath)) {
            unlink($idCardPath);
        }
        
        $user->setIdCard(null);
        $dm->flush();
        
        $this->addFlash('warning', 'La carte d\'identité de ' . $user->getUsername() . ' a été rejetée.');
        
        return $this->redirectToRoute('admin_id_verification');
    }
}
