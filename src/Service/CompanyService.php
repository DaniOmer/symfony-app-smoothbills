<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class CompanyService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function manageCompany(FormInterface $form, Address $address, Company $company, User $user): void
    {
        $address->setZipcode($form->get('address')->getData()->getZipcode());
        $address->setCity($form->get('address')->getData()->getCity());
        $address->setCountry($form->get('address')->getData()->getCountry());
        $address->setAddress($form->get('address')->getData()->getAddress());
        $this->entityManager->persist($address);

        $company->setAddress($address);
        $this->entityManager->persist($company);

        $user->setCompany($company);
        $this->entityManager->persist($user);

        $this->entityManager->flush();
        $this->entityManager->flush();
    }
}