<?php

namespace App\Controller;

use App\Document\Chat;
use App\Document\Message;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
            'user1' => $user1,
            'user2' => $user2
        ]) ?? $dm->getRepository(Chat::class)->findOneBy([
            'user1' => $user2,
            'user2' => $user1
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

        return $this->render('chat/chat.html.twig', [
            'chat' => $chat,
            'messages' => $chat->getMessages()
        ]);
    }

    #[Route('/chat/{chatId}/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(string $chatId, Request $request, DocumentManager $dm, SessionInterface $session): Response
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        $chat = $dm->getRepository(Chat::class)->find($chatId);
        if (!$chat) {
            return $this->redirectToRoute('home');
        }

        $content = $request->request->get('content');
        if (!$content) {
            return $this->redirectToRoute('chat_view', ['chatId' => $chatId]);
        }

        $sender = $dm->getRepository(User::class)->find($currentUser['id']);
        $message = new Message($chat, $sender, $content);

        $dm->persist($message);
        $dm->flush();

        return $this->redirectToRoute('chat_view', ['chatId' => $chatId]);
    }

    #[Route('/message/{messageId}/delete', name: 'delete_message', methods: ['POST'])]
    public function deleteMessage(string $messageId, DocumentManager $dm, SessionInterface $session): Response
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        $message = $dm->getRepository(Message::class)->find($messageId);
        if (!$message) {
            return $this->redirectToRoute('home');
        }

        if ($message->getSender()->getId() !== $currentUser['id']) {
            return $this->redirectToRoute('chat_view', ['chatId' => $message->getChat()->getId()]);
        }

        $dm->remove($message);
        $dm->flush();

        return $this->redirectToRoute('chat_view', ['chatId' => $message->getChat()->getId()]);
    }
}
