<?php

namespace App\Controller;

use App\Document\Chat;
use App\Document\Message;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ChatController extends AbstractController
{
    #[Route('/chat/start/{userId}', name: 'start_chat', methods: ['GET'])]
    public function startChat(string $userId, DocumentManager $dm, SessionInterface $session): Response
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        $user1 = $dm->getRepository(User::class)->find($currentUser['id']);
        $user2 = $dm->getRepository(User::class)->find($userId);

        if (!$user1 || !$user2) {
            return $this->redirectToRoute('home');
        }

        $existingChat = $dm->getRepository(Chat::class)->findOneBy([
            '$or' => [
                ['user1' => $user1, 'user2' => $user2],
                ['user1' => $user2, 'user2' => $user1]
            ]
        ]);

        if (!$existingChat) {
            $chat = new Chat($user1, $user2);
            $dm->persist($chat);
            $dm->flush();
        } else {
            $chat = $existingChat;
        }

        return $this->redirectToRoute('chat_view', ['chatId' => $chat->getId()]);
    }

    #[Route('/chat/{chatId}', name: 'chat_view', methods: ['GET'])]
    public function viewChat(string $chatId, DocumentManager $dm, SessionInterface $session): Response
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        $chat = $dm->getRepository(Chat::class)->find($chatId);
        if (!$chat) {
            return $this->redirectToRoute('home');
        }

        if ($chat->getUser1()->getId() !== $currentUser['id'] && $chat->getUser2()->getId() !== $currentUser['id']) {
            return $this->redirectToRoute('home');
        }

        $messages = $dm->getRepository(Message::class)->findBy(['chat' => $chat], ['timestamp' => 'ASC']);

        return $this->render('chat/chat.html.twig', [
            'chat' => $chat,
            'messages' => $messages,
            'mercure_public_url' => $this->getParameter('mercure.public_url')
        ]);
    }

    #[Route('/chat/{chatId}/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(string $chatId, Request $request, DocumentManager $dm, SessionInterface $session, HubInterface $hub): JsonResponse
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $chat = $dm->getRepository(Chat::class)->find($chatId);
        if (!$chat) {
            return new JsonResponse(['error' => 'Chat not found'], Response::HTTP_NOT_FOUND);
        }

        $content = json_decode($request->getContent(), true)['content'] ?? '';
        if (empty($content)) {
            return new JsonResponse(['error' => 'Message content cannot be empty'], Response::HTTP_BAD_REQUEST);
        }

        $sender = $dm->getRepository(User::class)->find($currentUser['id']);
        $message = new Message($chat, $sender, $content);

        $dm->persist($message);
        $dm->flush();

        $messageData = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sender' => [
                'id' => $sender->getId(),
                'username' => $sender->getUsername(),
                'picture' => $sender->getPicture() ?: 'default.png',
            ],
            'timestamp' => $message->getTimestamp()->format('H:i'),
        ];

        $update = new Update(
            'chat/' . $chatId,
            json_encode($messageData)
        );
        
        $hub->publish($update);

        return new JsonResponse($messageData);
    }

    #[Route('/message/{messageId}/delete', name: 'delete_message', methods: ['DELETE'])]
    public function deleteMessage(string $messageId, DocumentManager $dm, SessionInterface $session, HubInterface $hub): JsonResponse
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $message = $dm->getRepository(Message::class)->find($messageId);
        if (!$message) {
            return new JsonResponse(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        if ($message->getSender()->getId() !== $currentUser['id']) {
            return new JsonResponse(['error' => 'Permission denied'], Response::HTTP_FORBIDDEN);
        }

        $chatId = $message->getChat()->getId();
        
        $dm->remove($message);
        $dm->flush();

        $update = new Update(
            'chat/' . $chatId,
            json_encode([
                'action' => 'delete',
                'messageId' => $messageId
            ])
        );
        
        $hub->publish($update);

        return new JsonResponse(['success' => true]);
    }
}