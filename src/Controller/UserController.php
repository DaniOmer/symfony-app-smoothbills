<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\User;
use App\Form\InvitationType;
use App\Form\UserType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\UserRegistrationChecker;
use App\Service\UserService;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/dashboard/settings/user')]
class UserController extends AbstractController
{
    use ProfileCompletionTrait;
    private $userRepository;
    private $userService;
    private $userRegistrationChecker;
    private $invitationRepository;

    public function __construct(UserRegistrationChecker $userRegistrationChecker, UserRepository $userRepository, UserService $userService, InvitationRepository $invitationRepository)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->invitationRepository = $invitationRepository;
    }

    #[Route('/manage', name: 'dashboard.settings.user.manage', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès refusé : Cette page est réservée aux administrateurs.');

        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $invitation = new Invitation();
        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $paginatedUsers = $this->userService->getPaginatedUsers($user, $page);

        $headers = ['Nom', 'Adresse mail', 'Role', 'Poste occupé', 'Date de création'];
        $rows = $this->userService->getUsersRows($user, $page);

        $config = [
            'rows' => $rows,
            'headers' => $headers,
            'users' => $paginatedUsers,
            'deleteRoute' => 'dashboard.customer.delete',
            'deleteFormTemplate' => 'dashboard/user/_delete_form.html.twig',
            'actions' => [],
            'form' => $form,
        ];
        
        return $this->render('dashboard/user/index.html.twig', $config);
    }

    #[Route('/profile', name: 'dashboard.settings.user.profile', methods: ['GET', 'POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informatiosn ont bien été mis à jour.');
            return $this->redirectToRoute('dashboard.settings.user.profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/user/update.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/invite', name: 'dashboard.settings.user.invite', methods: ['POST'])]
    public function addUserByInvitation(Request $request, JWTService $jWTService): Response 
    {
        $invitation = new Invitation();
        $form = $this->createForm(InvitationType::class, $invitation);
        $form->handleRequest($request);
        
        $user = $this->getUser();
        $ownerId = $user->getId();

        $company = $user->getCompany();
        $companyId = $company->getId();

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                return $this->json(['error' => 'Cette adresse mail n\'est pas disponible.'], Response::HTTP_CONFLICT);
            }

            $existingInvitation = $this->invitationRepository->findOneBy(['email' => $email]);
            if ($existingInvitation) {
                return $this->json(['error' => 'Une invitation a déjà été envoyée à cet utilisateur.'], Response::HTTP_CONFLICT);
            }

            $token = $jWTService->createToken(['email' => $email, 'companyId' => $companyId, 'ownerId' => $ownerId], 86400);
            $encodedToken = base64_encode($token);
            $inviteUrl = $this->generateUrl('site.register.by.invitation', ['token' => $encodedToken], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->userService->createInvitation($user, $company, $invitation, $inviteUrl, $email, $token);
            
            return $this->json(['success' => 'Invitation envoyée avec succès!'], Response::HTTP_OK);
        }

        return $this->render('dashboard/user/invite.html.twig', [
            'invitation' => $invitation,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'dashboard.settings.user.delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard.settings.user.manage', [], Response::HTTP_SEE_OTHER);
    }
}
