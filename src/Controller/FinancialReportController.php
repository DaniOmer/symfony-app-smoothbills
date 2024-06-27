<?php

namespace App\Controller;

use App\Form\SalesReportFormType;
use App\Service\FinancialReportService;
use App\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/financial/report')]
class FinancialReportController extends AbstractController
{
    private $salesReportService;
    private $invoiceService;

    public function __construct(FinancialReportService $financialReportService, InvoiceService $invoiceService)
    {
        $this->salesReportService = $financialReportService;
        $this->invoiceService = $invoiceService;
    }

    #[Route('/', name: 'dashboard.financial.report')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SalesReportFormType::class);
        $form->handleRequest($request);

        $defaultStartDate = new \DateTime('-30 days');
        $defaultEndDate = new \DateTime();
        $company = $this->getUser()->getCompany();

        $sales = $this->salesReportService->generateSalesReportByPeriod($defaultStartDate, $defaultEndDate, $company);

        return $this->render('dashboard/financial_report/index.html.twig', [
            'invoices' => $sales['invoices'],
            'totalAmountHT' => $sales['totalAmountHT'],
            'totalAmountTTC' => $sales['totalAmountTTC'],
            'startDate' => $defaultStartDate,
            'endDate' => $defaultEndDate,
            'form' => $form,
        ]);
    }
}
