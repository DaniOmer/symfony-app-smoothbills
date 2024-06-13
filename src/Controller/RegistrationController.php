<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\Role;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, #[Autowire('%admin_email%')] private $adminEmail)
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

            $this->emailVerifier->sendEmailConfirmation('site.verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->adminEmail, 'Smoothbill'))
                    ->to($user->getEmail())
                    ->subject('Bienvenue chez Smoothbill !')
                    ->htmlTemplate('site/registration/mail/verification_email.html.twig')
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
            return $this->redirectToRoute('site.login');
        }

        return $this->render('site/registration/success.html.twig');
    }

    #[Route('/verify/email', name: 'site.verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, MailerService $mailerService, SessionInterface $session): Response
    {
        $currentTimeStamp = time();
        $userUid = $request->query->get('id');
        $expire = $request->query->get('expires');
        $token = bin2hex(random_bytes(32));

        if (null === $userUid) {
            $this->addFlash('verify_email_error', 'Le lien de vérification est invalide.');
            return $this->redirectToRoute('site.register');
        }

        $user = $userRepository->findOneBy(['uid' => $userUid]);
        if(!$user) {
            $this->addFlash('verify_email_error', 'Le lien de vérification est invalide.');
            return $this->redirectToRoute('site.register');
        }

        if($user->isVerified()){
            return $this->redirectToRoute('site.login');
        }

        if($currentTimeStamp > $expire){

            $this->emailVerifier->sendEmailConfirmation('site.verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->adminEmail, 'Smoothbill'))
                    ->to($user->getEmail())
                    ->subject('Nouveau lien de vérification')
                    ->htmlTemplate('site/registration/mail/verification_email.html.twig')
            );

            $session->set('verification_token', $token);

            return $this->redirectToRoute('site.resend.verification.email', ['token' => $token]);
        }

        try {

            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $mailerService->sendWelcomeEmail($user, 'Votre compte a été validé !', ['user' => $user,], 'site/registration/mail/confirmation_email.html.twig');
            return $this->redirectToRoute('site.login');

        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('site.register');

        }
    }


    #[Route('/resend/verification/email', name: 'site.resend.verification.email')]
    public function resendVerificationEmail(Request $request, SessionInterface $session): Response
    {
        $token = $request->query->get('token');
        $sessionToken = $session->get('verification_token');

        if ($token !== $sessionToken) {
            return $this->redirectToRoute('site.login');
        }

        return $this->render('site/registration/resend_verification.html.twig');
    }
}