<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use App\Service\SubscriptionService;
use App\Service\CompanyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/dashboard/settings/subscriptions')]
class SubscriptionController extends AbstractController
{
    #[Route('/', name: 'dashboard.settings.subscriptions.index')]
    public function index(SubscriptionService $subscriptionService): Response
    {
        $subscriptions = $subscriptionService->getAllSubscriptionsWithFeatures();

        return $this->render('dashboard/settings/subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
        ]);
    }

    #[Route('/change', name: 'dashboard.settings.subscriptions.change')]
    public function changeSubscription(Request $request, CompanyService $companyService, SubscriptionRepository $subscriptionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès refusé : Cette page est réservée aux administrateurs.');

        $company = $this->getUser()->getCompany();
        $subscriptionId = $request->query->get('subscriptionId');

        if (!$subscriptionId) {
            $this->addFlash('error', 'Abonnement non trouvé.');
            return $this->redirectToRoute('dashboard.settings.subscriptions.index');
        }

        $newSubscription = $subscriptionRepository->find($subscriptionId);

        if ($newSubscription) {
            $companyService->changeSubscription($company, $newSubscription);
            $this->addFlash('success', 'Abonnement changé avec succès.');
        } else {
            $this->addFlash('error', 'Abonnement non trouvé.');
        }

        return $this->redirectToRoute('dashboard.settings.subscriptions.index');
    }
}