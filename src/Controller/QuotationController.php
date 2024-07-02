<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Quotation;
use App\Entity\Service;
use App\Form\QuotationType;
use App\Service\CsvExporter;
use App\Service\PdfGeneratorService;
use App\Service\QuotationService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\SubscriptionService;

#[Route('/dashboard/quotation')]
class QuotationController extends AbstractController
{
    use ProfileCompletionTrait;

    private $quotationService;
    private $userRegistrationChecker;
    private $csvExporter;
    private $subscriptionService;

    public function __construct(
        QuotationService $quotationService,
        UserRegistrationChecker $userRegistrationChecker,
        CsvExporter $csvExporter,
        SubscriptionService $subscriptionService
    ) {
        $this->quotationService = $quotationService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->csvExporter = $csvExporter;
        $this->subscriptionService = $subscriptionService;
    }

    #[Route('/', name: 'dashboard.quotation.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);

        $paginateQuotations = $this->quotationService->getPaginatedQuotations($user, $page);
        $totalQuotation = $this->quotationService->getQuotationTotalCount($user);
        $acceptedQuotation = $this->quotationService->getQuotationAcceptedCount($user);
        $rejectedQuotation = $this->quotationService->getQuotationRejectedCount($user);
        $conversionRate = $this->quotationService->getConversionRate($user);

        $headers = ['N° devis', 'Status', 'Client', 'Envoyé le'];
        $rows = $this->quotationService->getQuotationsRows($user, $page);

        $statusCounts = [
            'accepted' => $acceptedQuotation,
            'rejected' => $rejectedQuotation,
        ];

        $actions = [
            ['route' => 'dashboard.quotation.show', 'label' => 'Afficher'],
            ['route' => 'dashboard.quotation.export', 'label' => 'Exporter'],
        ];

        foreach ($rows as &$row) {
            if ($row['status'] !== 'Accepted') {
                $actions[] = ['route' => 'dashboard.quotation.edit', 'label' => 'Modifier'];
            }
            if ($row['sendingDate'] === '') {
                $actions[] = ['route' => 'dashboard.quotation.send', 'label' => 'Envoyer'];
            }
        }

        $config = [
            'statusCounts' => $statusCounts,
            'headers' => $headers,
            'rows' => $rows,
            'quotations' => $paginateQuotations,
            'totalQuotation' => $totalQuotation,
            'conversionRate' => $conversionRate,
            'actions' => $actions,
            'deleteFormTemplate' => 'dashboard/quotation/_delete_form.html.twig',
            'deleteRoute' => 'dashboard.quotation.delete',
        ];

        return $this->render('dashboard/quotation/index.html.twig', $config);
    }


    #[Route('/new', name: 'dashboard.quotation.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        if (!$this->subscriptionService->canCreateQuotation()) {
            $this->addFlash('error_quotation', 'Vous avez atteint la limite de devis pour votre abonnement actuel.');
            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        $quotation = new Quotation();
        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        $user = $this->getUser();
        $company = $user->getCompany();

        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['company' => $company]);
        $service = $entityManager->getRepository(Service::class)->findOneBy(['company' => $company]);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $this->quotationService->processQuotation($quotation, $form, $company);

                $sendOption = $form->get('sendOption')->getData();
                if ($sendOption === 'Maintenant') {
                    $token = $this->quotationService->generateQuotationValidationToken($quotation);
                    $encodedToken = base64_encode($token);
                    $quotationValidationUrl = $this->generateUrl('site.home.validation.quotation', ['token' => $encodedToken], UrlGeneratorInterface::ABSOLUTE_URL);

                    $this->quotationService->sendQuotationMail($quotation, $quotationValidationUrl);
                }

                $this->addFlash('success_quotation', 'Le devis a été créé avec succès.');
                return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error_quotation', 'Une erreur est survenue lors de la création du devis.');
            }
        }

        return $this->render('dashboard/quotation/new.html.twig', [
            'quotation' => $quotation,
            'customer' => $customer,
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{uid}', name: 'dashboard.quotation.show', methods: ['GET'])]
    public function show(Quotation $quotation, PdfGeneratorService $pdfGeneratorService): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $data = $this->quotationService->getQuotationDataForPdf($quotation);
        $twigTemplate = $this->renderView('dashboard/quotation/pdf/quotation_template.html.twig', $data);

        $pdfContent = $pdfGeneratorService->showPdf($twigTemplate);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Route('/{uid}/edit', name: 'dashboard.quotation.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        if ($quotation->getQuotationStatus()->getName() === 'Accepted') {
            $this->addFlash('error_quotation', 'Vous ne pouvez pas modifier une quotation acceptée.');
            return $this->redirectToRoute('dashboard.quotation.index');
        }

        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success_quotation', 'Le devis a été modifié avec succès.');

            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/edit.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
            'deleteFormTemplate' => 'dashboard/quotation/_delete_form.html.twig',
            'deleteRoute' => 'dashboard.quotation.delete',
        ]);
    }


    #[Route('/{id}', name: 'dashboard.quotation.delete', methods: ['POST'])]
    public function delete(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quotation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($quotation);
            $entityManager->flush();
            $this->addFlash('success_quotation', 'Le devis a été supprimé avec succès.');
        }

        return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{uid}/export', name: 'dashboard.quotation.export', methods: ['GET'])]
    public function exportQuotation(Quotation $quotation): Response
    {
        if ($this->subscriptionService->isCurrentSubscription('Freemium')) {
            $this->addFlash('error_quotation', 'Vous avez pas accès à cette fonctionnalité avec l\'abonnement freemuim.');
            return $this->redirectToRoute('dashboard.quotation.index');
        }
        $csvData = $this->csvExporter->exportQuotation($quotation);

        return new Response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quotation_' . $quotation->getUid() . '.csv"',
        ]);
    }

    #[Route('/export/all', name: 'dashboard.quotation.export_all', methods: ['GET'])]
    public function exportAllQuotations(): Response
    {
        return $this->quotationService->exportAllQuotations();
    }

    #[Route('/{uid}/send', name: 'dashboard.quotation.send', methods: ['GET'])]
    public function sendQuotation(Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        if ($quotation->getSendingDate() === null) {
            $token = $this->quotationService->generateQuotationValidationToken($quotation);
            $encodedToken = base64_encode($token);
            $quotationValidationUrl = $this->generateUrl('site.home.validation.quotation', ['token' => $encodedToken], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->quotationService->sendQuotationMail($quotation, $quotationValidationUrl);

            $quotation->setSendingDate(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success_quotation', 'Le devis a été envoyé avec succès.');
        }

        return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
    }
}