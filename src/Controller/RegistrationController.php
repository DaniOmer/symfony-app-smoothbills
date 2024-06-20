<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\Role;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    public function __construct(
        private UserService $userService, 
        private UserRepository $userRepository,
        private EmailVerifier $emailVerifier
        )
    {
    }

    #[Route('/register', name: 'site.register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->isGranted('ROLE_USER')) {
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

            $this->userService->sendVerificationEmail($user);
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
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, SessionInterface $session): Response
    {
        $userUid = $request->query->get('id');
        $expire = $request->query->get('expires');
        $token = bin2hex(random_bytes(32));
        $user = $this->userRepository->findOneBy(['uid' => $userUid]);

        try {

            $this->emailVerifier->verify($request, $user, $expire);
            $this->userService->sendAccountValidationConfirmation($user);
            return $this->redirectToRoute('site.login');

        } catch (\LogicException $exception) {

            if ($exception->getMessage() === 'Le lien de vérification a expiré.') {
                $this->userService->resendVerificationEmail($user);
                $token = $session->get('verification_token');
                return $this->redirectToRoute('site.resend.verification.email', ['token' => $token]);

            } elseif($exception->getMessage() === 'Votre compte a déjà été vérifié.'){
                $this->addFlash('verify_email_error', $exception->getMessage());
                return $this->redirectToRoute('site.login');

            }else{
                $this->addFlash('verify_email_error', $exception->getMessage());
                return $this->redirectToRoute('site.register');
            }

        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('site.login');

        }
    }


    #[Route('/resend/verification/email', name: 'site.resend.verification.email')]
    public function resendVerificationEmail(Request $request, SessionInterface $session): Response
    {
        $token = $request->query->get('token');
        $sessionToken = $session->get('verification_token');
        
        if (!$token || !$sessionToken || $token !== $sessionToken) {
            return $this->redirectToRoute('site.login');
        }

        return $this->render('site/registration/resend_verification.html.twig');
    }

    #[Route('/complete/registration', name: 'site.complete_registration')]
    public function completeRegistration(Request $request): Response
    {
        $user = $this->getUser();
        if ($user->isRegistrationComplete()) {
            return $this->redirectToRoute('dashboard.home');
        }

        return $this->render('site/registration/complete_registration.html.twig');
    }
}
