<?php

namespace App\Controller;

use App\Document\Conversation;
use App\Document\Message;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ChatController extends AbstractController
{
    private DocumentManager $dm;
    private Security $security;

    public function __construct(DocumentManager $dm, Security $security)
    {
        $this->dm = $dm;
        $this->security = $security;
    }

    #[Route('/chat', name: 'chat_index')]
    public function index(): Response
    {
        // Récupérer l'utilisateur courant
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer toutes les conversations de l'utilisateur
        $conversations = $this->dm->createQueryBuilder(Conversation::class)
            ->field('participants')->equals($currentUser->getId())
            ->sort('lastMessageAt', 'DESC')
            ->getQuery()
            ->execute();

        // Pour chaque conversation, récupérer l'autre participant et le dernier message
        $conversationData = [];
        foreach ($conversations as $conversation) {
            $participants = $conversation->getParticipants();
            $otherParticipantId = ($participants[0] === $currentUser->getId()) ? $participants[1] : $participants[0];
            
            $otherUser = $this->dm->getRepository(User::class)->find($otherParticipantId);
            
            $lastMessage = $this->dm->createQueryBuilder(Message::class)
                ->field('conversationId')->equals($conversation->getId())
                ->sort('createdAt', 'DESC')
                ->limit(1)
                ->getQuery()
                ->getSingleResult();
            
            $conversationData[] = [
                'conversation' => $conversation,
                'otherUser' => $otherUser,
                'lastMessage' => $lastMessage,
            ];
        }

        return $this->render('chat/index.html.twig', [
            'conversationData' => $conversationData,
        ]);
    }

    #[Route('/chat/{id}', name: 'chat_conversation')]
    public function conversation(string $id): Response
    {
        // Récupérer l'utilisateur courant
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la conversation
        $conversation = $this->dm->getRepository(Conversation::class)->find($id);
        if (!$conversation || !in_array($currentUser->getId(), $conversation->getParticipants())) {
            throw $this->createNotFoundException('Conversation not found');
        }

        // Récupérer l'autre participant
        $participants = $conversation->getParticipants();
        $otherParticipantId = ($participants[0] === $currentUser->getId()) ? $participants[1] : $participants[0];
        $otherUser = $this->dm->getRepository(User::class)->find($otherParticipantId);

        // Récupérer tous les messages de cette conversation
        $messages = $this->dm->createQueryBuilder(Message::class)
            ->field('conversationId')->equals($id)
            ->sort('createdAt', 'ASC')
            ->getQuery()
            ->execute();

        // Marquer tous les messages non lus comme lus
        foreach ($messages as $message) {
            if (!$message->isRead() && $message->getSenderId() !== $currentUser->getId()) {
                $message->setRead(true);
                $this->dm->persist($message);
            }
        }
        $this->dm->flush();

        return $this->render('chat/conversation.html.twig', [
            'conversation' => $conversation,
            'otherUser' => $otherUser,
            'messages' => $messages,
            'currentUser' => $currentUser,
        ]);
    }

    #[Route('/chat/{id}/send', name: 'chat_send_message', methods: ['POST'])]
    public function sendMessage(Request $request, string $id): Response
    {
        // Récupérer l'utilisateur courant
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer la conversation
        $conversation = $this->dm->getRepository(Conversation::class)->find($id);
        if (!$conversation || !in_array($currentUser->getId(), $conversation->getParticipants())) {
            throw $this->createNotFoundException('Conversation not found');
        }

        // Créer et sauvegarder le nouveau message
        $content = trim($request->request->get('content'));
        if (!empty($content)) {
            $message = new Message();
            $message->setConversationId($conversation->getId())
                ->setSenderId($currentUser->getId())
                ->setContent($content);
            
            $this->dm->persist($message);
            
            // Mettre à jour la date du dernier message de la conversation
            $conversation->setLastMessageAt(new \DateTime());
            $this->dm->persist($conversation);
            
            $this->dm->flush();
        }

        return $this->redirectToRoute('chat_conversation', ['id' => $id]);
    }

    #[Route('/chat/new/{userId}', name: 'chat_new_conversation')]
    public function newConversation(string $userId): Response
    {
        // Récupérer l'utilisateur courant
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur cible existe
        $otherUser = $this->dm->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            throw $this->createNotFoundException('User not found');
        }

        // Vérifier si une conversation existe déjà entre ces deux utilisateurs
        $existingConversation = $this->dm->createQueryBuilder(Conversation::class)
            ->field('participants')->all([$currentUser->getId(), $userId])
            ->getQuery()
            ->execute()
            ->toArray();

        if (count($existingConversation) > 0) {
            return $this->redirectToRoute('chat_conversation', ['id' => reset($existingConversation)->getId()]);
        }

        // Créer une nouvelle conversation
        $conversation = new Conversation();
        $conversation->addParticipant($currentUser->getId())
            ->addParticipant($userId);
        
        $this->dm->persist($conversation);
        $this->dm->flush();

        return $this->redirectToRoute('chat_conversation', ['id' => $conversation->getId()]);
    }
}