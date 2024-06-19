<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Service\ServiceService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/service')]
class ServiceController extends AbstractController
{
    use ProfileCompletionTrait;
    private $serviceService;
    private $userRegistrationChecker;

    public function __construct(ServiceService $serviceService, UserRegistrationChecker $userRegistrationChecker)
    {
        $this->serviceService = $serviceService;
        $this->userRegistrationChecker = $userRegistrationChecker;
    }

    #[Route('/', name: 'dashboard.service.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $services = $this->serviceService->getPaginatedServices($user, $page);

        $headers = ['Nom', 'Description', 'Prix', 'Durée estimée', 'Statut'];
        $rows = $this->serviceService->getServicesRows($user, $page);

        $config = [
            'headers' => $headers,
            'rows' => $rows,
            'actions' => [
                ['label' => 'Modifier', 'route' => 'dashboard.service.edit'],
            ],
            'deleteFormTemplate' => 'dashboard/service/_delete_form.html.twig',
            'deleteRoute' => 'dashboard.service.delete',
            'services' => $services,
        ];

        return $this->render('dashboard/service/index.html.twig', $config);
    }



    #[Route('/new', name: 'dashboard.service.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->serviceService->createService($form, $service, $user);

            return $this->redirectToRoute('dashboard.service.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard.service.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard.service.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/service/edit.html.twig', [
            'form' => $form,
            'service' => $service,
            'deleteRoute' => 'dashboard.service.delete',
            'deleteFormTemplate' => 'dashboard/service/_delete_form.html.twig',
        ]);
    }

    #[Route('/{id}', name: 'dashboard.service.delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $service->getId(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard.service.index', [], Response::HTTP_SEE_OTHER);
    }
}