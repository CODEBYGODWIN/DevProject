<?php

namespace App\Service;

use App\Document\User;
use App\Document\Inou;
use Doctrine\ODM\MongoDB\DocumentManager;

class MatchingService
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function findMatches(User $currentUser): array
    {
        $currentInou = $currentUser->getInou();
        if (!$currentInou) {
            return [];
        }

        $userRepo = $this->dm->getRepository(User::class);
        $allUsers = $userRepo->findAll();
        $matches = [];

        $currentOrientation = $currentInou->getQ1();
        $currentGoal = $currentInou->getQ2();
        $currentGender = $currentUser->getGender();

        foreach ($allUsers as $user) {
            if ((string) $user->getId() === (string) $currentUser->getId()) {
                continue;
            }

            $userInou = $user->getInou();
            if (!$userInou) {
                continue;
            }

            $userOrientation = $userInou->getQ1();
            $userGender = $user->getGender();

            if ($currentGoal === 'trouver le grand amour' || $currentGoal === 'Les deux') {
                if (!$this->isSexuallyCompatible($currentOrientation, $currentGender, $userOrientation, $userGender)) {
                    if ($currentGoal === 'trouver le grand amour') {
                        continue;
                    }
                    $isRomanticMatch = false;
                } else {
                    $isRomanticMatch = true;
                }
            } else {
                $isRomanticMatch = false;
            }

            $affinity = $this->calculateAffinity($currentInou, $userInou);

            $matches[] = [
                'user' => $user, 
                'affinity' => $affinity,
                'isRomanticMatch' => $isRomanticMatch
            ];
        }

        usort($matches, fn($a, $b) => $b['affinity'] <=> $a['affinity']);
        
        return $matches;
    }

    private function isSexuallyCompatible(string $orientation1, string $gender1, string $orientation2, string $gender2): bool
    {
        switch ($orientation1) {
            case 'Hétérosexuel':
                $oppositeGender = $gender1 === 'male' ? 'female' : 'male';
                return $gender2 === $oppositeGender && 
                       ($orientation2 === 'Hétérosexuel' || $orientation2 === 'Bisexuel');
                
            case 'Homosexuel':
                return $gender1 === $gender2 && 
                       ($orientation2 === 'Homosexuel' || $orientation2 === 'Bisexuel');
                
            case 'Bisexuel':
                if ($gender2 === 'male') {
                    return ($orientation2 === 'Hétérosexuel' && $gender1 === 'female') ||
                           ($orientation2 === 'Homosexuel' && $gender1 === 'male') ||
                           $orientation2 === 'Bisexuel';
                }
                if ($gender2 === 'female') {
                    return ($orientation2 === 'Hétérosexuel' && $gender1 === 'male') ||
                           ($orientation2 === 'Homosexuel' && $gender1 === 'female') ||
                           $orientation2 === 'Bisexuel';
                }
                return false;
                
            case 'Asexuel':
                return false;
                
            default:
                return false;
        }
    }

    private function calculateAffinity(Inou $inou1, Inou $inou2): int
    {
        $score = 0;
        // Comparer uniquement les questions 3 à 20 (pas les questions pivots)
        for ($i = 3; $i <= 20; $i++) {
            $getter = "getQ$i";
            if ($inou1->$getter() === $inou2->$getter()) {
                $score++;
            }
        }
        return $score;
    }
}