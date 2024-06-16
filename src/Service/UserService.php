<?php

namespace App\Service;

use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;

class UserService
{

    public function __construct(
        private EmailVerifier $emailVerifier, 
        #[Autowire('%admin_email%')] private $adminEmail, 
        private MailerService $mailerService,)
    {
    }

    public function sendVerificationEmail(object $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'site.verify_email', 
            $user,
            (new TemplatedEmail())
                ->from(new Address($this->adminEmail, 'Smoothbill'))
                ->to($user->getEmail())
                ->subject('Bienvenue chez Smoothbill !')
                ->htmlTemplate('site/registration/mail/verification_email.html.twig')
            );
    }

    public function resendVerificationEmail(object $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'site.verify_email', 
            $user,
            (new TemplatedEmail())
                ->from(new Address($this->adminEmail, 'Smoothbill'))
                ->to($user->getEmail())
                ->subject('Nouveau lien de vérification')
                ->htmlTemplate('site/registration/mail/verification_email.html.twig')
            );
    }

    public function sendAccountValidationConfirmation(object $user): void
    {
        $this->mailerService->sendWelcomeEmail(
            $user, 
            'site/registration/mail/confirmation_email.html.twig',
            ['user' => $user,], 
            'Votre compte a été validé !', 
        );
    }
}