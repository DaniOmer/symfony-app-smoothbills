<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Vente;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class FinancialReportService
{
    private $entityManager;
    private $invoiceRepository;
    private $invoiceService;

    public function __construct(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository, InvoiceService $invoiceService)
    {
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
    }

    public function generateSalesReportByPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Company $company)
    {
        $sales = $this->invoiceRepository->findInvoicesByPeriod($startDate, $endDate, $company);
        $invoices = [];
        $totalAmountHT = 0;
        $totalAmountTTC = 0;

        if (empty($sales)) {
            return [
                'invoices' => [],
                'totalAmountHT' => 0,
                'totalAmountTTC' => 0,
            ];
        }

        foreach($sales as $invoice){
            $invoiceDetails = $this->invoiceService->getInvoiceDetails($invoice);
            $invoices[] = $invoiceDetails;
            $totalAmountHT += $invoiceDetails['amount_ht'];
            $totalAmountTTC += $invoiceDetails['amount_ttc'];
        }

        return [
            'invoices' => $invoices,
            'totalAmountHT' => $totalAmountHT,
            'totalAmountTTC' => $totalAmountTTC
        ];
    }

}
