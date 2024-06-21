<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Form\CompleteRegistrationFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\Role;
use App\Service\JWTService;
use App\Service\RegistrationService;
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
        private RegistrationService $registrationService, 
        private UserRepository $userRepository,
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $entityManager
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

            $this->registrationService->sendVerificationEmail($user);
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
            $this->registrationService->sendAccountValidationConfirmation($user);
            return $this->redirectToRoute('site.login');

        } catch (\LogicException $exception) {

            if ($exception->getMessage() === 'Le lien de vérification a expiré.') {
                $this->registrationService->resendVerificationEmail($user);
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

    #[Route('/register/by/invitation/{token}', name: 'site.register.by.invitation')]
    public function registerWithToken($token, Request $request, JWTService $jWTService, UserPasswordHasherInterface $userPasswordHasher,): Response
    {
        $decodedToken = base64_decode($token);
        $jwtData = $jWTService->parseToken($decodedToken);

        if (!$jwtData || !isset($jwtData['email']) || !isset($jwtData['companyId'])) {
            throw $this->createNotFoundException('Invalid or missing JWT data');
        }

        $invitation = $this->entityManager->getRepository(Invitation::class)->findOneBy(['token' => $decodedToken]);

        if (!$invitation || $invitation->getExpireAt() < new \DateTime()) {
            throw $this->createNotFoundException('Invitation not found or expired');
        }

        $form = $this->createForm(CompleteRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = new User();
            $user->setEmail($invitation->getEmail());
            $user->setFirstName($form->get('firstName')->getData());
            $user->setLastName($form->get('lastName')->getData());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setRoles([$invitation->getRole()]);

            $company = $this->entityManager->getRepository(Company::class)->find($jwtData['companyId']);
            $user->setCompany($company);

            $this->entityManager->persist($user);
            $this->entityManager->remove($invitation);
            $this->entityManager->flush();

            return $this->redirectToRoute('site.login');
        }

        return $this->render('site/registration/complete_registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}