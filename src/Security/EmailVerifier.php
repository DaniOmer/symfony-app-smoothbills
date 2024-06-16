<?php

namespace App\Security;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => $user->getUid()]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());

        $user->setVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function verify(Request $request, object $user, string $expire): void
    {

        $this->guardAgainstInvalidUser($user);
        $this->guardAgainstAlreadyVerified($user);
        $this->guardAgainstExpiredLink($expire, $user);

        $this->handleEmailConfirmation($request, $user);
    }

    private function guardAgainstInvalidUser(?object $user): void
    {
        if (!$user) {
            throw new \LogicException('Le lien de vérification est invalide.');
        }
    }

    private function guardAgainstAlreadyVerified(object $user): void
    {
        if ($user->isVerified()) {
            throw new \LogicException('Votre compte a déjà été vérifié.');
        }
    }

    private function guardAgainstExpiredLink(string $expire, $user): void
    {
        $currentTimeStamp = time();
        if ($currentTimeStamp > $expire) {
            throw new \LogicException('Le lien de vérification a expiré.');
        }
    }
}
