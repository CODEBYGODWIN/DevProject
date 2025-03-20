<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Chat;
use App\Document\Message;
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
        
        $chats = $dm->getRepository(Chat::class)->findBy([
            '$or' => [
                ['user1' => $userEntity],
                ['user2' => $userEntity]
            ]
        ]);
        
        $chatPartnerIds = [];
        $formattedChats = [];

        foreach ($chats as $chat) {
            $partner = ($chat->getUser1()->getId() === $userEntity->getId()) 
                ? $chat->getUser2() : $chat->getUser1();
            
            $chatPartnerIds[] = $partner->getId();
            
            $unreadCount = $dm->createQueryBuilder(Message::class)
                ->field('chat')->references($chat)
                ->field('sender')->references($partner)
                ->field('read')->equals(false)
                ->count()
                ->getQuery()
                ->execute();
            
            $formattedChats[] = [
                'id' => $chat->getId(),
                'partner' => $partner,
                'unreadCount' => $unreadCount
            ];
        }
        
        $matches = $matchingService->findMatches($userEntity);
        
        $matchesWithPercentage = array_map(function($match) use ($chatPartnerIds) {
            $percentage = ($match['affinity'] / 18) * 100;
            
            return [
                'user' => $match['user'],
                'affinity' => $match['affinity'],
                'percentage' => round($percentage, 1),
                'colorClass' => $this->getColorClass($percentage),
                'isRomanticMatch' => $match['isRomanticMatch'] ?? false,
                'hasChat' => in_array($match['user']->getId(), $chatPartnerIds)
            ];
        }, $matches);
        
        return $this->render('home/home.html.twig', [
            'user' => $userEntity,
            'matches' => $matchesWithPercentage,
            'goal' => $userEntity->getInou()->getQ2(),
            'chats' => $formattedChats,
            'mercure_public_url' => $this->getParameter('mercure.public_url')
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
