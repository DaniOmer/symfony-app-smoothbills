<?php
// src/Controller/InvoiceController.php

namespace App\Controller;

use App\Entity\Invoice;
use App\Service\InvoiceService;
use App\Repository\InvoiceRepository;
use App\Service\CsvExporter;
use App\Trait\ProfileCompletionTrait;
use App\Service\UserRegistrationChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/invoice')]
class InvoiceController extends AbstractController
{
    use ProfileCompletionTrait;

    private $userRegistrationChecker;
    private $csvExporter;
    private $invoiceService;
   
    public function __construct(InvoiceService $invoiceService, UserRegistrationChecker $userRegistrationChecker,CsvExporter $csvExporter)
    {
        $this->invoiceService = $invoiceService;
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->csvExporter = $csvExporter;
    }

    #[Route('/', name: 'dashboard.invoice.index', methods: ['GET'])]
    public function index(Request $request, InvoiceRepository $invoiceRepository): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $company = $user->getCompany();
        $page = $request->query->getInt('page', 1);
        $companyId = $company->getId();
        $paginateInvoices = $this->invoiceService->getPaginatedInvoices($user, $page);
        $headers = ['Numéro Facture', 'Date Facture', 'Montant HT', 'Montant TTC', 'Status', 'Nom du Client'];
        $rows = $this->invoiceService->getInvoicesRows($user, $page);
        
        $statusCounts = [];
        $allStatusNames = $this->invoiceService->getAllInvoiceStatusNames();
        
        foreach ($allStatusNames as $statusName) {
            $statusCounts[$statusName] = $invoiceRepository->countInvoicesByStatus($statusName, $companyId);
        }
        
        $config = [
            'statusCounts' => $statusCounts,
            'headers' => $headers,
            'rows' => $rows,
            'invoices' => $paginateInvoices,
            'actions' => [
                ['route' => 'dashboard.invoice.show', 'label' => 'Afficher'],
            ]
        ];

        return $this->render('dashboard/invoice/index.html.twig', $config);
    }
        

    #[Route('/{uid}', name: 'dashboard.invoice.show', methods: ['GET'])]
    public function show(Invoice $invoice,InvoiceRepository $invoiceRepository): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $invoiceDetails = $this->invoiceService->getInvoiceDetails($invoice);

        return $this->render('dashboard/invoice/show.html.twig', [
            'invoice' => $invoiceDetails,
        ]);
    }   
}
