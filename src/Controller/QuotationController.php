<?php

namespace App\Controller;

use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use App\Form\QuotationType;
use App\Repository\QuotationRepository;
use App\Service\CsvExporter;
use App\Service\QuotationService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/quotation')]
class QuotationController extends AbstractController
{
    use ProfileCompletionTrait;

    private $quotationService;
    private $userRegistrationChecker;
    private $csvExporter;

    public function __construct(QuotationService $quotationService, UserRegistrationChecker $userRegistrationChecker, CsvExporter $csvExporter)
    {
        $this->quotationService = $quotationService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->csvExporter = $csvExporter;
    }

    #[Route('/', name: 'dashboard.quotation.index', methods: ['GET'])]
    public function index(QuotationRepository $quotationRepository, Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $page = $request->query->getInt('page', 1);
        $paginateQuotations = $this->quotationService->getPaginatedQuotations($page);

        $headers = ['Nom', 'Status', 'Client', 'Envoyé le'];
        $rows = $this->quotationService->getQuotationsRows($page);

        $user = $this->getUser();
        $company = $user->getCompany();

        $companyId = $company->getId();

        $totalInvoices = $quotationRepository->countTotalQuotations();

        $statusCounts = [
            'accepted' => $quotationRepository->countQuotationsByStatus('Accepté', $companyId),
            'rejected' => $quotationRepository->countQuotationsByStatus('Refusé', $companyId),
        ];

        $config = [
            'statusCounts' => $statusCounts,
            'headers' => $headers,
            'rows' => $rows,
            'quotations' => $paginateQuotations,
            'totalInvoices' => $totalInvoices,
            'actions' => [
                ['route' => 'dashboard.quotation.show', 'label' => 'Afficher'],
                ['route' => 'dashboard.quotation.edit', 'label' => 'Modifier'],
                ['route' => 'dashboard.quotation.export', 'label' => 'Exporter'],
            ],
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

        $quotation = new Quotation();
        $quotationStatus = new QuotationStatus();
        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);
        $user = $this->getUser();
        $company = $user->getCompany();

        if ($form->isSubmitted() && $form->isValid()) {
            $quotationStatus->setName($form->get('quotation_status')->getData()->getName());
            $entityManager->persist($quotationStatus);

            $user = $this->getUser();

            $quotation->setQuotationStatus($quotationStatus);
            $quotation->setCompany($company);
            $entityManager->persist($quotation);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis a été créé avec succès.');

            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/new.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
        ]);
    }

    #[Route('/{uid}', name: 'dashboard.quotation.show', methods: ['GET'])]
    public function show(Quotation $quotation): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        return $this->render('dashboard/quotation/show.html.twig', [
            'quotation' => $quotation,
        ]);
    }

    #[Route('/{uid}/edit', name: 'dashboard.quotation.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

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
        if ($this->isCsrfTokenValid('delete'.$quotation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($quotation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{uid}/export', name: 'dashboard.quotation.export', methods: ['GET'])]
    public function exportQuotation(Quotation $quotation): Response
    {
        $headers = ['Nom', 'Status', 'Client', 'Envoyé le'];
        $data = [[
            $quotation->getUid(),
            $quotation->getQuotationStatus()->getName(),
            $quotation->getCustomer()->getName(),
            $quotation->getDate()->format('Y-m-d H:i:s')
        ]];

        return $this->csvExporter->export($data, $headers, 'quotation_' . $quotation->getUid());
    }

    #[Route('/export/all', name: 'dashboard.quotation.export_all', methods: ['GET'])]
    public function exportAllQuotations(QuotationRepository $quotationRepository): Response
    {
        $quotations = $quotationRepository->findAll();
        $headers = ['ID', 'Nom', 'Status', 'Client', 'Envoyé le'];
        $dataExtractor = function(Quotation $quotation) {
            return [
                $quotation->getId(),
                $quotation->getUid(),
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getDate()->format('Y-m-d H:i:s')
            ];
        };

        return $this->csvExporter->exportEntities($quotations, $headers, $dataExtractor, 'all_quotations');
    }
}