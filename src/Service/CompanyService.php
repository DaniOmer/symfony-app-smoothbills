<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\CompanySubscription;
use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Exception;

class CompanyService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function manageCompany(FormInterface $form, Address $address, Company $company, User $user): void
    {
        $this->entityManager->beginTransaction();
        try {
            $address->setZipcode($form->get('address')->getData()->getZipcode());
            $address->setCity($form->get('address')->getData()->getCity());
            $address->setCountry($form->get('address')->getData()->getCountry());
            $address->setAddress($form->get('address')->getData()->getAddress());
            $this->entityManager->persist($address);

            $company->setAddress($address);
            $this->entityManager->persist($company);

            $user->setCompany($company);
            $this->entityManager->persist($user);

            $this->assignFreemiumSubscription($company);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function assignFreemiumSubscription(Company $company): void
    {
        try {
            $subscriptionRepository = $this->entityManager->getRepository(Subscription::class);
            $freemiumSubscription = $subscriptionRepository->findOneBy(['name' => 'Freemium']);

            if ($freemiumSubscription) {
                $companySubscription = new CompanySubscription();
                $companySubscription->setCompany($company);
                $companySubscription->setSubscription($freemiumSubscription);
                $companySubscription->setStartDate(new \DateTime());
                $companySubscription->setEndDate((new \DateTime())->modify('+30 days'));

                $companySubscription->setStripeStatus('Pending');
                $companySubscription->setStripePaymentMethod('none');
                $companySubscription->setStripeLastDigits('0000');
                $companySubscription->setStripeSubscriptionId('none');

                $company->setSubscription($companySubscription);

                $this->entityManager->persist($companySubscription);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function changeSubscription(Company $company, Subscription $newSubscription): void
    {
        $this->entityManager->beginTransaction();
        try {
            $currentSubscription = $company->getSubscription();
            if ($currentSubscription) {
                $currentSubscription->setEndDate(new \DateTime());
                $this->entityManager->persist($currentSubscription);
            }

            $newCompanySubscription = new CompanySubscription();
            $newCompanySubscription->setCompany($company);
            $newCompanySubscription->setSubscription($newSubscription);
            $newCompanySubscription->setStartDate(new \DateTime());
            $newCompanySubscription->setEndDate((new \DateTime())->modify('+30 days'));

            $newCompanySubscription->setStripeStatus('active');
            $newCompanySubscription->setStripePaymentMethod('stripe');
            $newCompanySubscription->setStripeLastDigits('4242');
            $newCompanySubscription->setStripeSubscriptionId('new_subscription_id');

            $company->setSubscription($newCompanySubscription);

            $this->entityManager->persist($newCompanySubscription);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
