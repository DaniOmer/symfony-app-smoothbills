<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\UserRegistrationChecker;
use App\Service\UserService;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/settings/user')]
class UserController extends AbstractController
{
    use ProfileCompletionTrait;
    private $userRepository;
    private $userService;
    private $userRegistrationChecker;

    public function __construct(UserRegistrationChecker $userRegistrationChecker, UserRepository $userRepository, UserService $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->userRegistrationChecker = $userRegistrationChecker;
    }

    #[Route('/manage', name: 'dashboard.settings.user.manage', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès refusé : Cette page est réservée aux administrateurs.');

        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

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

    // #[Route('/invitation', name: 'dashboard.settings.user.invitation', methods: ['GET', 'POST'])]
    // public function addUser(Request $request, EntityManagerInterface $entityManager): Response
    // {
    //     $user = new User();
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager->persist($user);
    //         $entityManager->flush();

    //         $this->addFlash('success', 'L\'utilisateur a bien été créer');
    //         return $this->redirectToRoute('dashboard.settings.user.invitation', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->render('user/new.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }


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
