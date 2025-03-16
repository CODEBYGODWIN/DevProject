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

        // Récupérer tous les utilisateurs
        $userRepo = $this->dm->getRepository(User::class);
        $allUsers = $userRepo->findAll();
        $matches = [];

        // Obtenir l'orientation et l'objectif de l'utilisateur actuel
        $currentOrientation = $currentInou->getQ1();
        $currentGoal = $currentInou->getQ2();
        $currentGender = $currentUser->getGender();

        foreach ($allUsers as $user) {
            // Ne pas matcher l'utilisateur avec lui-même
            if ((string) $user->getId() === (string) $currentUser->getId()) {
                continue;
            }

            $userInou = $user->getInou();
            if (!$userInou) {
                continue;
            }

            // Obtenir les informations de l'utilisateur potentiel
            $userOrientation = $userInou->getQ1();
            $userGender = $user->getGender();

            // Vérifier la compatibilité selon le but recherché
            if ($currentGoal === 'trouver le grand amour' || $currentGoal === 'Les deux') {
                // Pour les relations amoureuses, vérifier la compatibilité sexuelle
                if (!$this->isSexuallyCompatible($currentOrientation, $currentGender, $userOrientation, $userGender)) {
                    // Si on ne cherche QUE l'amour et qu'il n'y a pas compatibilité, passer au suivant
                    if ($currentGoal === 'trouver le grand amour') {
                        continue;
                    }
                    // Si on cherche les deux, on le compte comme match amical seulement
                    $isRomanticMatch = false;
                } else {
                    $isRomanticMatch = true;
                }
            } else {
                // Pour les amitiés uniquement, pas besoin de vérifier l'orientation
                $isRomanticMatch = false;
            }

            // Calculer l'affinité basée sur les questions 3 à 20
            $affinity = $this->calculateAffinity($currentInou, $userInou);

            // Ajouter aux matches avec informations supplémentaires
            $matches[] = [
                'user' => $user, 
                'affinity' => $affinity,
                'isRomanticMatch' => $isRomanticMatch
            ];
        }

        // Trier par affinité décroissante
        usort($matches, fn($a, $b) => $b['affinity'] <=> $a['affinity']);
        
        return $matches;
    }

    private function isSexuallyCompatible(string $orientation1, string $gender1, string $orientation2, string $gender2): bool
    {
        // Vérifier les compatibilités selon les orientations
        switch ($orientation1) {
            case 'Hétérosexuel':
                // Un hétéro est compatible avec le sexe opposé qui est aussi hétéro ou bi
                $oppositeGender = $gender1 === 'male' ? 'female' : 'male';
                return $gender2 === $oppositeGender && 
                       ($orientation2 === 'Hétérosexuel' || $orientation2 === 'Bisexuel');
                
            case 'Homosexuel':
                // Un homo est compatible avec le même sexe qui est aussi homo ou bi
                return $gender1 === $gender2 && 
                       ($orientation2 === 'Homosexuel' || $orientation2 === 'Bisexuel');
                
            case 'Bisexuel':
                // Un bi est compatible avec :
                // - Homme hétéro si l'utilisateur est une femme
                // - Femme hétéro si l'utilisateur est un homme
                // - Homo du même sexe
                // - Autre bi
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
                // Les asexuels ne sont pas inclus dans les matches amoureux
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