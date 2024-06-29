<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Vente;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class FinancialReportService
{
    private $entityManager;
    private $invoiceRepository;
    private $invoiceService;
    private $chartBuilder;

    public function __construct(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository, InvoiceService $invoiceService, ChartBuilderInterface $chartBuilder)
    {
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->chartBuilder = $chartBuilder;
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
                    'invoices' => []
                ];
            }
            $groupedInvoices[$date]['totalAmountHT'] += $invoice['amount_ht'];
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

        $labels = [];
        $data = [];

        foreach ($salesData as $daySales) {
            // $labels[] = $daySales['date'];
            // $data[] = $daySales['totalAmountHT'];
            // dd($daySales['date'], $daySales['totalAmountHT']);
            $salesByDate[$daySales['date']] = $daySales['totalAmountHT'];
        }

        foreach ($period as $date) {
            $formattedDate = $date->format('d-m-Y');
            $labels[] = $formattedDate;
            $data[] = $salesByDate[$formattedDate] ?? 0;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Ventes journalières',
                    'backgroundColor' => 'rgb(72, 88, 208)',
                    'borderColor' => 'rgb(72, 88, 208)',
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => max($data) + 1000,
                ],
            ],
        ]);

        return $chart;
    }
}
