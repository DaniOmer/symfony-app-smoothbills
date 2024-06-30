<?php

namespace App\Service;

use App\Repository\SubscriptionRepository;
use App\Repository\CompanySubscriptionRepository;
use App\Repository\QuotationRepository;
use Symfony\Bundle\SecurityBundle\Security;

class SubscriptionService
{
    private $subscriptionRepository;
    private $companySubscriptionRepository;
    private $quotationRepository;
    private $security;

    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        CompanySubscriptionRepository $companySubscriptionRepository,
        QuotationRepository $quotationRepository,
        Security $security
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->companySubscriptionRepository = $companySubscriptionRepository;
        $this->quotationRepository = $quotationRepository;
        $this->security = $security;
    }

    public function getAllSubscriptionsWithFeatures(): array
    {
        $subscriptions = $this->subscriptionRepository->findAll();
        $subscriptionData = [];

        $user = $this->security->getUser();
        $company = $user ? $user->getCompany() : null;
        $currentSubscription = $company ? $this->companySubscriptionRepository->findLatestSubscriptionForCompany($company->getId()) : null;
        $currentSubscriptionId = $currentSubscription ? $currentSubscription->getSubscription()->getId() : null;

        foreach ($subscriptions as $subscription) {
            $features = [];
            foreach ($subscription->getOptions() as $option) {
                $features[] = [
                    'text' => $option->getName(),
                    'active' => $option->getIsActive(),
                ];
            }

            $subscriptionData[] = [
                'id' => $subscription->getId(),
                'name' => $subscription->getName(),
                'price' => $subscription->getPrice(),
                'features' => $features,
                'isCurrentPlan' => $currentSubscriptionId === $subscription->getId(),
            ];
        }

        return $subscriptionData;
    }

    public function isCurrentSubscription(string $subscriptionName): bool
    {
        $user = $this->security->getUser();
        $company = $user ? $user->getCompany() : null;
        $subscription = $company ? $this->companySubscriptionRepository->findLatestSubscriptionForCompany($company->getId()) : null;

        return $subscription && $subscription->getSubscription()->getName() === $subscriptionName;
    }

    public function canAddCustomer(): bool
    {
        $user = $this->security->getUser();
        $company = $user ? $user->getCompany() : null;
        if ($company && $this->isCurrentSubscription('Freemium')) {
            return count($company->getCustomers()) < 5;
        }
        return true;
    }

    public function canCreateQuotation(): bool
    {
        $user = $this->security->getUser();
        $company = $user ? $user->getCompany() : null;
        if ($company && $this->isCurrentSubscription('Freemium')) {
            $quotationsThisMonth = $this->quotationRepository->countQuotationsForCompanyThisMonth($company->getId());
            return $quotationsThisMonth < 1;
        }
        return true;
    }
}
