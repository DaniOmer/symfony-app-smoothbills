<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/customer')]
class CustomerController extends AbstractController
{
    #[Route('/', name: 'dashboard.customer.index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $paginateCustumers = $customerRepository->paginateCustomers($page);
        
        $headers = ['Nom', 'Adresse mail', 'Téléphone', 'Type'];
        $rows = [];

        foreach ($paginateCustumers as $customer) {
            $rows[] = [
                'name' => $customer->getName(),
                'mail' => $customer->getMail(),
                'phone' => $customer->getPhone(),
                'type' => $customer->getType(),
                'id' => $customer->getId(),
            ];
        }

        return $this->render('dashboard/customer/index.html.twig', [
            'headers' => $headers,
            'rows' => $rows,
            'customers' => $paginateCustumers,
        ]);
    }

    #[Route('/new', name: 'dashboard.customer.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $address = new Address();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setZipcode($form->get('address')->getData()->getZipcode());
            $address->setCity($form->get('address')->getData()->getCity());
            $address->setCountry($form->get('address')->getData()->getCountry());
            $address->setAddress($form->get('address')->getData()->getAddress());
            $entityManager->persist($address);

            $customer->setCreatedBy($this->getUser());
            $customer->setCompany($this->getUser()->getCompany());
            $entityManager->persist($customer);
            
            $entityManager->flush();

            return $this->redirectToRoute('dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard.customer.show', methods: ['GET'])]
    public function show(Customer $customer): Response
    {
        return $this->render('dashboard/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard.customer.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard/dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard.customer.delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard/dashboard.customer.index', [], Response::HTTP_SEE_OTHER);
    }
}
