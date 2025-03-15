<?php

namespace App\Controller;

use App\Document\User;
use App\Service\MatchingService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(SessionInterface $session, DocumentManager $dm, MatchingService $matchingService): Response
    {
        $user = $session->get('user');
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        
        $userEntity = $dm->getRepository(User::class)->find($user['id']);
        
        if (!$userEntity) {
            return $this->redirectToRoute('login');
        }
        
        if (!$userEntity->getInou()) {
            return $this->redirectToRoute('questionnaire');
        }
        
        $matches = $matchingService->findMatches($userEntity);
        
        $matchesWithPercentage = array_map(function($match) {
            $percentage = ($match['affinity'] / 18) * 100;
            
            return [
                'user' => $match['user'],
                'affinity' => $match['affinity'],
                'percentage' => round($percentage, 1),
                'colorClass' => $this->getColorClass($percentage),
                'isRomanticMatch' => $match['isRomanticMatch'] ?? false
            ];
        }, $matches);
        
        return $this->render('home/home.html.twig', [
            'user' => $userEntity,
            'matches' => $matchesWithPercentage,
            'goal' => $userEntity->getInou()->getQ2()
        ]);
    }
    
    private function getColorClass(float $percentage): string
    {
        if ($percentage > 60) {
            return 'text-success';
        } elseif ($percentage >= 30) {
            return 'text-warning';
        } else {
            return 'text-danger';
        }
    }
}