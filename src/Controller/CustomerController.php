<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use App\Service\CustomerService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\SubscriptionService;
use App\Service\CsvExporter;

#[Route('/dashboard/customer')]
class CustomerController extends AbstractController
{
    use ProfileCompletionTrait;

    private $customerService;
    private $userRegistrationChecker;
    private $subscriptionService;
    private $csvExporter;

    public function __construct(CustomerService $customerService, UserRegistrationChecker $userRegistrationChecker, SubscriptionService $subscriptionService, CsvExporter $csvExporter)
    {
        $this->customerService = $customerService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->subscriptionService = $subscriptionService;
        $this->csvExporter = $csvExporter;
    }

    #[Route('/', name: 'dashboard.customer.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $customers = $this->customerService->getPaginatedCustomers($user, $page);

        $headers = ['Nom', 'Adresse mail', 'Téléphone', 'Type'];
        $rows = $this->customerService->getCustomersRows($user, $page);

        $config = [
            'headers' => $headers,
            'rows' => $rows,
            'actions' => [
                ['label' => 'Modifier', 'route' => 'dashboard.customer.edit'],
            ],
            'deleteFormTemplate' => 'dashboard/customer/_delete_form.html.twig',
            'deleteRoute' => 'dashboard.customer.delete',
            'customers' => $customers,
        ];

        return $this->render('dashboard/customer/index.html.twig', $config);
    }

    #[Route('/new', name: 'dashboard.customer.new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        if (!$this->subscriptionService->canAddCustomer()) {
            $this->addFlash('error_customer', 'Vous avez atteint la limite de clients pour votre abonnement actuel.');
            return $this->redirectToRoute('dashboard.customer.index');
        }

        $user = $this->getUser();
        $customer = new Customer();
        $address = new Address();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->customerService->createCustomer($form, $address, $customer, $user);
                $this->addFlash('success_customer', 'Le client a été créé avec succès.');
                return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error_customer', 'Une erreur est survenue lors de la création du client.');
            }
        }

        return $this->render('dashboard/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }


    #[Route('/{uid}/edit', name: 'dashboard.customer.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success_customer', 'Le client a été mis à jour avec succès.');
            return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/customer/edit.html.twig', [
            'form' => $form,
            'customer' => $customer,
            'deleteRoute' => 'dashboard.customer.delete',
            'deleteFormTemplate' => 'dashboard/customer/_delete_form.html.twig',
        ]);
    }

    #[Route('/{id}', name: 'dashboard.customer.delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $customer->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export/all', name: 'dashboard.customer.export_all', methods: ['GET'])]
    public function exportAllServices(CustomerRepository $customerRepository): Response
    {

        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }
        if ($this->subscriptionService->isCurrentSubscription('Freemium')) {
            $this->addFlash('error_customer', 'Vous avez pas accès à cette fonctionnalité avec l\'abonnement freemuim.');
            return $this->redirectToRoute('dashboard.service.index');
        }

        $customers = $customerRepository->findAll();
        $headers = ['Nom', 'Adresse mail', 'Téléphone', 'Type'];
        $dataExtractor = function (Customer $customer) {
            return [
                $customer->getName(),
                $customer->getMail(),
                $customer->getPhone(),
                $customer->getType(),
            ];
        };

        return $this->csvExporter->exportEntities($customers, $headers, $dataExtractor, 'all_customers.csv');
    }
}