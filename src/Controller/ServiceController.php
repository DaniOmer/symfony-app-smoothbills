<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Service\ServiceService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use App\Repository\ServiceRepository;
use App\Service\CsvExporter;
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
    private $csvExporter;

    public function __construct(ServiceService $serviceService, UserRegistrationChecker $userRegistrationChecker, CsvExporter $csvExporter)
    {
        $this->serviceService = $serviceService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->csvExporter = $csvExporter;
    }
    #[Route('/', name: 'dashboard.service.index', methods: ['GET'])]
    public function index(ServiceRepository $serviceRepository, Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $statusColors = [
            'Active' => 'bg-green-100 text-green-800',
            'Inactive' => 'bg-red-100 text-red-800',
            'Pendding' => 'bg-yellow-100 text-yellow-800',
        ];

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $services = $this->serviceService->getPaginatedServices($user, $page);

        $headers = ['Nom', 'Prix', 'Durée estimée', 'Statut', 'Créé le'];
        $rows = $this->serviceService->getServicesRows($user, $page);

        $company = $user->getCompany();
        $companyId = $company->getId();

        $totalServices = $serviceRepository->countTotalServicesByCompany($user);

        $statusCounts = [
            'active' => $serviceRepository->countServicesByStatus('Active', $companyId),
            'inactive' => $serviceRepository->countServicesByStatus('Inactive', $companyId),
        ];

        $headersTopTransaction = ['id' => 'ID', 'service' => 'Service', 'date' => 'Date', 'price' => 'Prix'];
        $topTransactionsData = array_map(function ($transaction) {
            return [
                'id' => $transaction['id'],
                'service' => strlen($transaction['service']) > 20 ? substr($transaction['service'], 0, 20) . '...' : $transaction['service'],
                'date' => $transaction['date']->format('d M'),
                'price' => number_format($transaction['price'], 2) . '€'
            ];
        }, $serviceRepository->getTop3TransactionsByHighestPrice());

        $topServicesData = array_map(function ($service) {
            return [
                'title' => $service['title'],
                'sales' => $service['sales'],
                'revenue' => number_format($service['revenue'], 2) . '€'
            ];
        }, $serviceRepository->getTop3ServicesBySales());

        $config = [
            'statusCounts' => $statusCounts,
            'headers' => $headers,
            'rows' => $rows,
            'services' => $services,
            'totalServices' => $totalServices,
            'actions' => [
                ['route' => 'dashboard.service.show', 'label' => 'Afficher'],
                ['route' => 'dashboard.service.edit', 'label' => 'Modifier'],
            ],
            'deleteFormTemplate' => 'dashboard/service/_delete_form.html.twig',
            'deleteRoute' => 'dashboard.service.delete',
            'statusColors' => $statusColors,
            'headersTopTransaction' => $headersTopTransaction,
            'topTransactionsData' => $topTransactionsData,
            'topServicesData' => $topServicesData,
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
            $this->addFlash('success', 'Le service a été créé avec succès.');
            return $this->redirectToRoute('dashboard.service.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/service/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{uid}', name: 'dashboard.service.show', methods: ['GET'])]
    public function show(Service $service): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        return $this->render('dashboard/service/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{uid}/edit', name: 'dashboard.service.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le service a été modifié avec succès.');
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

    #[Route('/export/all', name: 'dashboard.service.export_all', methods: ['GET'])]
    public function exportAllServices(ServiceRepository $serviceRepository): Response
    {
        $services = $serviceRepository->findAll();
        $headers = ['ID', 'Nom', 'Prix', 'Durée estimée', 'Statut', 'Description'];
        $dataExtractor = function (Service $service) {
            return [
                $service->getId(),
                $service->getName(),
                $service->getPrice(),
                $service->getEstimatedDuration(),
                $service->getServiceStatus()->getName(),
                $service->getDescription(),
            ];
        };

        return $this->csvExporter->exportEntities($services, $headers, $dataExtractor, 'all_services');
    }
}