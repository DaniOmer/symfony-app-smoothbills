<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'site.register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('site.home');
        }
        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([Role::ROLE_ADMIN]);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('site.verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@login.smoothbill.com', 'Smoothbill'))
                    ->to($user->getEmail())
                    ->subject('Validez votre adresse email')
                    ->htmlTemplate('site/registration/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('site.register.success');
        }

        return $this->render('site/registration/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/register/success', name: 'site.register.success')]
    public function registerSuccess(Request $request): Response
    {
        $referer = $request->headers->get('referer');
        if (!$referer || strpos($referer, $this->generateUrl('site.register')) === false) {
            return $this->redirectToRoute('site.register');
        }

        return $this->render('site/registration/success.html.twig');
    }

    #[Route('/verify/email', name: 'site.verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('site.register');
        }

        $this->addFlash('success', 'Votre adresse mail a été vérifié avec succès.');
        return $this->redirectToRoute('dashboard.home');
    }
}
