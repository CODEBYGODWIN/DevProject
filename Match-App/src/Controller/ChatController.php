<?php

namespace App\Controller;

use App\Document\Chat;
use App\Document\Message;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;

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

        // Try to find existing chat between these users
        $existingChat = $dm->createQueryBuilder(Chat::class)
            ->addOr($dm->expr()->field('user1')->equals($user1)->field('user2')->equals($user2))
            ->addOr($dm->expr()->field('user1')->equals($user2)->field('user2')->equals($user1))
            ->getQuery()
            ->getSingleResult();

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

        // Check if current user is part of this chat
        if ($chat->getUser1()->getId() !== $currentUser['id'] && $chat->getUser2()->getId() !== $currentUser['id']) {
            return $this->redirectToRoute('home');
        }

        $messages = $dm->getRepository(Message::class)->findBy(['chat' => $chat], ['timestamp' => 'ASC']);

        // Créer un JWT pour l'autorisation Mercure
        $jwtSecret = $this->getParameter('mercure.jwt_secret');
        
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecret)
        );

        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->withClaim('mercure', [
                'subscribe' => ['chat/' . $chatId]
            ])
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->getToken($config->signer(), $config->signingKey());

        // Créer une réponse avec le cookie JWT
        $response = $this->render('chat/chat.html.twig', [
            'chat' => $chat,
            'messages' => $messages,
            'mercure_public_url' => $this->getParameter('mercure.public_url')
        ]);

        // Ajouter le cookie JWT pour l'autorisation Mercure
        $response->headers->setCookie(
            new Cookie(
                'mercureAuthorization',
                $token->toString(),
                (new \DateTime())->add(new \DateInterval('PT1H')),
                '/',
                null,
                false,
                true,
                false,
                'strict'
            )
        );

        return $response;
    }

    #[Route('/chat/{chatId}/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(string $chatId, Request $request, DocumentManager $dm, SessionInterface $session, HubInterface $hub): Response
    {
        $currentUser = $session->get('user');
        if (!$currentUser) {
            return $this->redirectToRoute('login');
        }

        $chat = $dm->getRepository(Chat::class)->find($chatId);
        if (!$chat) {
            return $this->redirectToRoute('home');
        }

        $content = trim($request->request->get('content'));
        if (empty($content)) {
            return $this->redirectToRoute('chat_view', ['chatId' => $chatId]);
        }

        $sender = $dm->getRepository(User::class)->find($currentUser['id']);
        $message = new Message($chat, $sender, $content);

        $dm->persist($message);
        $dm->persist($chat);
        $dm->flush();

        // Créer un JWT pour la publication
        $jwtSecret = $this->getParameter('mercure.jwt_secret');
        
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecret)
        );

        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->withClaim('mercure', [
                'publish' => ['chat/' . $chatId]
            ])
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->getToken($config->signer(), $config->signingKey());

        $update = new Update(
            'chat/' . $chatId,
            json_encode([
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sender' => [
                    'id' => $sender->getId(),
                    'username' => $sender->getUsername(),
                    'picture' => $sender->getPicture() ?: 'default.png',
                ],
                'timestamp' => $message->getTimestamp()->format('H:i'),
            ])
        );
        
        try {
            $hub->publish($update);
        } catch (\Exception $e) {
            // Log l'erreur ou gérer l'exception si nécessaire
        }

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

        // Only allow deletion of own messages
        if ($message->getSender()->getId() !== $currentUser['id']) {
            return $this->redirectToRoute('chat_view', ['chatId' => $message->getChat()->getId()]);
        }

        $chatId = $message->getChat()->getId();
        
        $dm->remove($message);
        $dm->flush();

        return $this->redirectToRoute('chat_view', ['chatId' => $chatId]);
    }
}