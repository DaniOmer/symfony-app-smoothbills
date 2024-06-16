<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    public function __construct(#[Autowire('%admin_email%')] private string $adminEmail, private readonly MailerInterface $mailer)
    {
    }

    public function sendWelcomeEmail(User $user,  $template, $context=[], $subject='no-reply',): void
    {
        $email = (new NotificationEmail())
            ->from(new Address($this->adminEmail, 'Smoothbill'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
}