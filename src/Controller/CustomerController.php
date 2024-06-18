<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\CustomerType;
use App\Service\CustomerService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/customer')]
class CustomerController extends AbstractController
{
    use ProfileCompletionTrait;

    private $customerService;
    private $userRegistrationChecker;

    public function __construct(CustomerService $customerService, UserRegistrationChecker $userRegistrationChecker)
    {
        $this->customerService = $customerService;
        $this->userRegistrationChecker = $userRegistrationChecker;
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

        $user = $this->getUser();
        $customer = new Customer();
        $address = new Address();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->customerService->createCustomer($form, $address, $customer, $user);

            return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
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
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
    }
}
