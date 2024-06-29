<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Vente;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FinancialReportService
{
    private $entityManager;
    private $invoiceRepository;
    private $invoiceService;
    private $chartJsService;
    private $validator;
    private $taxService;

    public function __construct(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository, InvoiceService $invoiceService, ChartJsService $chartJsService, ValidatorInterface $validator, TaxService $taxService)
    {
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->chartJsService = $chartJsService;
        $this->validator = $validator;
        $this->taxService = $taxService;
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

    private function validateDate($date, ValidatorInterface $validator)
    {
        $constraints = new Assert\Date();
        $violations = $validator->validate($date, $constraints);

        if ($date == null) {
            return null;
        }

        if (count($violations) > 0) {
            return null;
        }

        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getStartAndEndDate(Request $request): array
    {
        $startDateParam = $request->query->get('startDate');
        $endDateParam = $request->query->get('endDate');

        $startDate = $this->validateDate($startDateParam, $this->validator);
        $endDate = $this->validateDate($endDateParam, $this->validator);

        if (!$startDate || !$endDate) {
            $startDate = new \DateTime('-30 days');
            $endDate = new \DateTime();
        }

        return [ $startDate, $endDate ];
    }

    public function generateServicePerformanceReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate, $company): array
    {
        $services = $this->invoiceRepository->getMostlySalesServices($startDate, $endDate, $company);

        foreach ($services as &$service){
            $service['priceWithTax'] = $this->taxService->applyTva($service['price']);
        }

        return $services;
    }

}
