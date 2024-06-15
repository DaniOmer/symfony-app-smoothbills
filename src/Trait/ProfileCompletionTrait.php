<?php

namespace App\Trait;

use App\Service\UserRegistrationChecker;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait ProfileCompletionTrait
{
    private function isProfileComplete(UserRegistrationChecker $userRegistrationChecker): RedirectResponse
    {
        if(!$userRegistrationChecker->isRegistrationComplete()){
            $this->addFlash('error', 'Vous devez compléter les informations de votre entreprise pour continuer.');
            return $this->redirectToRoute('dashboard.settings.company');
        }
    }
}