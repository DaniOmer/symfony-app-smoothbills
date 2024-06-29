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
    private $chartJsService;

    public function __construct(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository, InvoiceService $invoiceService, ChartJsService $chartJsService)
    {
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->chartJsService = $chartJsService;
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

    public function groupSalesByDate(\DateTime $startDate, \DateTime $endDate, Company $company): array
    {
        $invoices = $this->generateSalesReportByPeriod($startDate, $endDate, $company)['invoices'];
        $groupedInvoices = [];
        
        foreach ($invoices as $invoice) {
            $date = $invoice['invoice_date'];
            if (!isset($groupedInvoices[$date])) {
                $groupedInvoices[$date] = [
                    'date' => $date,
                    'totalAmountHT' => 0,
                    'totalAmountTTC' => 0,
                    'invoices' => []
                ];
            }
            $groupedInvoices[$date]['totalAmountHT'] += $invoice['amount_ht'];
            $groupedInvoices[$date]['totalAmountTTC'] += $invoice['amount_ttc'];
            $groupedInvoices[$date]['invoices'][] = $invoice;
        }
        
        return $groupedInvoices;
    }

    public function generateSalesChartByDay(\DateTime $startDate, \DateTime $endDate, Company $company)
    {
        $salesData = $this->groupSalesByDate($startDate, $endDate, $company);

        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            (clone $endDate)->modify('+1 day')
        );
        $chartType = 'TYPE_LINE';
        $labelTitle = 'Ventes journalières TTC (€)';
        $labels = [];
        $data = [];

        foreach ($salesData as $daySales) {
            $salesByDate[$daySales['date']] = $daySales['totalAmountTTC'];
        }

        foreach ($period as $date) {
            $formattedDate = $date->format('d-m-Y');
            $labels[] = $formattedDate;
            $data[] = $salesByDate[$formattedDate] ?? 0;
        }

        $chart = $this->chartJsService->createChart($labelTitle, $labels, $data, $chartType);

        return $chart;
    }
}
