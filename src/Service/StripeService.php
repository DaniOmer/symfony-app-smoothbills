<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;

class StripeService
{
    private $stripe;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, $stripeSecretKey)
    {
        $this->stripe = new StripeClient($stripeSecretKey);
        $this->entityManager = $entityManager;
    }

    public function changeSubscription(Company $company, Subscription $subscription)
    {
        // Get the current subscription
        $companySubscription = $company->getCurrentSubscription();
        $stripeSubscriptionId = $companySubscription->getStripeSubscriptionId();

        if ($stripeSubscriptionId) {
            // Update the subscription on Stripe
            $this->stripe->subscriptions->update($stripeSubscriptionId, [
                'items' => [
                    ['price' => $subscription->getStripePriceId()],
                ],
            ]);

            // Update the local database
            $companySubscription->setSubscription($subscription);
            $this->entityManager->persist($companySubscription);
            $this->entityManager->flush();
        } else {
            throw new \Exception('No Stripe subscription found for this company');
        }
    }
}
