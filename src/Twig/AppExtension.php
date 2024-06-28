<?php

namespace App\Twig;

use App\Repository\CompanySubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $companySubscriptionRepository;
    private $security;

    public function __construct(CompanySubscriptionRepository $companySubscriptionRepository, Security $security)
    {
        $this->companySubscriptionRepository = $companySubscriptionRepository;
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        $company = $user ? $user->getCompany() : null;
        $subscription = $company ? $this->companySubscriptionRepository->findLatestSubscriptionForCompany($company->getId()) : null;
        $subscriptionName = $subscription ? $subscription->getSubscription()->getName() : 'None';

        return [
            'subscriptionName' => $subscriptionName,
        ];
    }
}
