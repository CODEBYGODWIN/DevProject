<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Inou;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class InouController extends AbstractController
{
    #[Route('/questionnaire', name: 'questionnaire')]
    public function questionnaire(Request $request, DocumentManager $dm, SessionInterface $session): Response
    {
        $user = $session->get('user');
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $userEntity = $dm->getRepository(User::class)->find($user['id']);
        if (!$userEntity) {
            return $this->redirectToRoute('login');
        }

        if ($userEntity->getInou()) {
            return $this->redirectToRoute('home');
        }

        $inou = new Inou();
        $inou->setUserId($userEntity);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            for ($i = 1; $i <= 20; $i++) {
                $setter = 'setQ' . $i;
                if (method_exists($inou, $setter)) {
                    $inou->$setter($data["q$i"] ?? '');
                }
            }

            $dm->persist($inou);
            $dm->flush();

            $userEntity->setInou($inou);
            $dm->persist($userEntity);
            $dm->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('inou.html.twig');
    }
}