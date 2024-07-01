<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\MailerService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/site')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        #[Autowire('%admin_email%')] private $adminEmail,
        private MailerService $mailerService,
    ) {
    }

    #[Route('/forgot-password', name: 'site.forgot_password')]
    public function request(Request $request, UserRepository $userRepository, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $resetToken = $tokenGenerator->generateToken();
                $user->setResetToken($resetToken);
                $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));

                $entityManager->persist($user);
                $entityManager->flush();

                $resetPasswordUrl = $this->generateUrl('site.reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new TemplatedEmail())
                    ->from(new Address($this->adminEmail, 'Smoothbill'))
                    ->to($user->getEmail())
                    ->subject('Votre demande de réinitialisation de mot de passe')
                    ->htmlTemplate('site/reset_password/mail/reset_password.html.twig')
                    ->context([
                        'resetPasswordUrl' => $resetPasswordUrl,
                    ]);

                $mailer->send($email);

                $this->addFlash('success', 'Un email vous a été envoyé avec un lien pour réinitialiser votre mot de passe.');
                return $this->redirectToRoute('site.login');
            }

            $this->addFlash('error', 'Aucun compte n\'a été trouvé avec cette adresse email. Veuillez réessayer.');
        }

        return $this->render('site/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'site.reset_password')]
    public function reset(Request $request, string $token, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Le lien de réinitialisation de mot de passe est invalide ou a expiré. Veuillez réessayer.');
            return $this->redirectToRoute('site.forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->get('first')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');

            return $this->redirectToRoute('site.login');
        }

        return $this->render('site/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token,
        ]);
    }
}