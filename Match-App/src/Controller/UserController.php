<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/add-user', name: 'add_user')]
    public function addUser(DocumentManager $dm): Response
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');

        $dm->persist($user);
        $dm->flush();

        return new Response('User ajouté avec ID : ' . $user->getId());
    }

    #[Route('/users', name: 'list_users')]
    public function listUsers(DocumentManager $dm): Response
    {
        $users = $dm->getRepository(User::class)->findAll();

        return $this->json($users);
    }
}