<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Inou;
use App\Form\LoginType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;


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

        $inou = new Inou();
        $inou->setUserId($userEntity);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $inou->setQ1($data['q1'] ?? '');
            $inou->setQ2($data['q2'] ?? '');
            $inou->setQ3($data['q3'] ?? '');
            $inou->setQ4($data['q4'] ?? '');
            $inou->setQ5($data['q5'] ?? '');
            $inou->setQ6($data['q6'] ?? '');
            $inou->setQ7($data['q7'] ?? '');
            $inou->setQ8($data['q8'] ?? '');
            $inou->setQ9($data['q9'] ?? '');
            $inou->setQ10($data['q10'] ?? '');
            $inou->setQ11($data['q11'] ?? '');
            $inou->setQ12($data['q12'] ?? '');
            $inou->setQ13($data['q13'] ?? '');
            $inou->setQ14($data['q14'] ?? '');
            $inou->setQ15($data['q15'] ?? '');
            $inou->setQ16($data['q16'] ?? '');
            $inou->setQ17($data['q17'] ?? '');
            $inou->setQ18($data['q18'] ?? '');
            $inou->setQ19($data['q19'] ?? '');
            $inou->setQ20($data['q20'] ?? '');

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