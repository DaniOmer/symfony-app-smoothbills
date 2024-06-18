<?php

namespace App\Controller;

use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use App\Form\QuotationType;
use App\Repository\QuotationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/quotation')]
class QuotationController extends AbstractController
{
    #[Route('/', name: 'dashboard.quotation.index', methods: ['GET'])]
    public function index(QuotationRepository $quotationRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $paginateQuotations = $quotationRepository->paginateQuotations($page);

        $headers = ['Nom', 'Status', 'Client', 'Envoyé le'];
        $rows = [];

        $user = $this->getUser();
        $company = $user->getCompany();
        if (!$company) {
            throw $this->createNotFoundException('No company associated with this user.');
        }
        $companyId = $company->getId();

        $totalInvoices = $quotationRepository->countTotalQuotations();

        $statusCounts = [
            'accepted' => $quotationRepository->countQuotationsByStatus('Accepté', $companyId),
            'rejected' => $quotationRepository->countQuotationsByStatus('Refusé', $companyId),
        ];

        foreach ($paginateQuotations as $quotation) {
            $quotationEntity = $quotationRepository->findQuotationEntityById($quotation->getId());

            $rows[] = [
                'id' => $quotation->getId(),
                'name' => $quotationEntity->getUid(),
                'status' => $quotation->getQuotationStatus()->getName(),
                'client' => $quotation->getCustomer()->getName(),
                'date' => $quotation->getDate()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->render('dashboard/quotation/index.html.twig', [
            'statusCounts' => $statusCounts,
            'headers' => $headers,
            'rows' => $rows,
            'quotations' => $paginateQuotations,
            'totalInvoices' => $totalInvoices,
        ]);
    }

    #[Route('/new', name: 'dashboard.quotation.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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
            $companyData = $user->getCompany();
            $company->setDenomination($companyData->getDenomination());
            $company->setSiren($companyData->getSiren());
            $company->setSiret($companyData->getSiret());
            $company->setTvaNumber($companyData->getTvaNumber());
            $company->setRcsNumber($companyData->getRcsNumber());
            $company->setPhoneNumber($companyData->getPhoneNumber());
            $company->setMail($companyData->getMail());
            $company->setCreationDate($companyData->getCreationDate());
            $company->setRegisteredSocial($companyData->getRegisteredSocial());
            $company->setSector($companyData->getSector());
            $company->setLogo($companyData->getLogo());
            $company->setSigning($companyData->getSigning());

            $entityManager->persist($company);

            $quotation->setQuotationStatus($quotationStatus);
            $quotation->setCompany($company);
            $entityManager->persist($quotation);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/new.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard.quotation.show', methods: ['GET'])]
    public function show(Quotation $quotation): Response
    {
        return $this->render('dashboard/quotation/show.html.twig', [
            'quotation' => $quotation,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard.quotation.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/edit.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
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

    #[Route('/{id}/export', name: 'dashboard.quotation.export', methods: ['GET'])]
    public function exportQuotation(Quotation $quotation): Response
    {
        $response = new StreamedResponse(function() use ($quotation) {
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, ['Nom', 'Status', 'Client', 'Envoyé le'], ';');

            fputcsv($handle, [
                $quotation->getUid(),
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getDate()->format('Y-m-d H:i:s')
            ], ';');

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="quotation_'.$quotation->getUid().'.csv"');

        return $response;
    }

    #[Route('/export/all', name: 'dashboard.quotation.export_all', methods: ['GET'])]
    public function exportAllQuotations(QuotationRepository $quotationRepository): Response
    {
        $quotations = $quotationRepository->findAll();

        $response = new StreamedResponse(function() use ($quotations) {
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, ['ID', 'Nom', 'Status', 'Client', 'Envoyé le'], ';');

            foreach ($quotations as $quotation) {
                fputcsv($handle, [
                    $quotation->getId(),
                    $quotation->getUid(),
                    $quotation->getQuotationStatus()->getName(),
                    $quotation->getCustomer()->getName(),
                    $quotation->getDate()->format('Y-m-d H:i:s')
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="all_quotations.csv"');

        return $response;
    }
}