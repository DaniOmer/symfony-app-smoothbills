<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormInterface;

class CustomerService
{
    private $customerRepository;
    private $entityManager;

    public function __construct(CustomerRepository $customerRepository, EntityManagerInterface $entityManager)
    {
        $this->customerRepository = $customerRepository;
        $this->entityManager = $entityManager;
    }

    public function getPaginatedCustomers(User $user, $page): PaginationInterface
    {
        $paginateCustumers = $this->customerRepository->paginateCustomersByCompany($user, $page);

        return $paginateCustumers;
    }

    public function getCustomersRows(User $user, $page): Array
    {
        $rows = [];

        foreach ($this->getPaginatedCustomers($user, $page) as $customer) {
            $rows[] = [
                'name' => $customer->getName(),
                'mail' => $customer->getMail(),
                'phone' => $customer->getPhone(),
                'type' => $customer->getType(),
                'uid' => $customer->getUid(),
                'id' => $customer->getId(),
            ];
        }

        return $rows;
    }

    public function createCustomer(FormInterface $form, Address $address, Customer $customer, User $user): void
    {
        $address->setZipcode($form->get('address')->getData()->getZipcode());
        $address->setCity($form->get('address')->getData()->getCity());
        $address->setCountry($form->get('address')->getData()->getCountry());
        $address->setAddress($form->get('address')->getData()->getAddress());
        $this->entityManager->persist($address);

        $customer->setCompany($user->getCompany());
        $this->entityManager->persist($customer);
            
        $this->entityManager->flush();

        $this->entityManager->flush();
    }
}