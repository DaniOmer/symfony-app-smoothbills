<?php

namespace App\Controller;

use App\Form\SalesReportFormType;
use App\Service\FinancialReportService;
use App\Service\PdfGeneratorService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/financial')]
class FinancialReportController extends AbstractController
{
    use ProfileCompletionTrait;
    private $userRegistrationChecker;
    private $financialReportService;
    private $pdfGeneratorService;

    public function __construct(FinancialReportService $financialReportService, UserRegistrationChecker $userRegistrationChecker, PdfGeneratorService $pdfGeneratorService)
    {
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->financialReportService = $financialReportService;
        $this->pdfGeneratorService = $pdfGeneratorService;
    }

    #[Route('/', name: 'dashboard.financial.report')]
    public function index(): Response
    {
        return $this->render('dashboard/financial_report/index.html.twig');
    }

    #[Route('/sales/by/period', name: 'dashboard.financial.report.sales', methods: ['GET', 'POST'])]
    public function getSalesReport(Request $request): Response
    {
        $form = $this->createForm(SalesReportFormType::class);
        $form->handleRequest($request);

        $company = $this->getUser()->getCompany();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];

            return $this->redirect($this->generateUrl('dashboard.financial.report.sales', [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]));
        }

        [ $startDate, $endDate ] = $this->financialReportService->getStartAndEndDate($request);

        $sales = $this->financialReportService->generateSalesReportByPeriod($startDate, $endDate, $company);
        $salesByDayChart = $this->financialReportService->generateSalesChartByDay($startDate, $endDate, $company);

        $data = [
            'invoices' => $sales['invoices'],
            'totalAmountHT' => $sales['totalAmountHT'],
            'totalAmountTTC' => $sales['totalAmountTTC'],
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y/m/d'),
            'form' => $form,
            'chart' => $salesByDayChart,
        ];

        return $this->render('dashboard/financial_report/sales_report/index.html.twig', $data);
    }

    #[Route('/sales/by/period/download', name: 'dashboard.financial.report.sales.download', methods: ['GET'])]
    public function exportSalesReport(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $company = $this->getUser()->getCompany();

        [ $startDate, $endDate ] = $this->financialReportService->getStartAndEndDate($request);

        $sales = $this->financialReportService->generateSalesReportByPeriod($startDate, $endDate, $company);

        $data = [
            'invoices' => $sales['invoices'],
            'totalAmountHT' => $sales['totalAmountHT'],
            'totalAmountTTC' => $sales['totalAmountTTC'],
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y/m/d'),
        ];

        $filename = 'sales_report_from_'.$startDate->format('Y-m-d').'_to_'.$endDate->format('Y-m-d');
        $twigTemplate = $this->renderView('dashboard/financial_report/sales_report/pdf/export_template.html.twig', $data);

        return $this->pdfGeneratorService->downloadPdf($twigTemplate, $filename);
    }


    #[Route('/product/performance', name: 'dashboard.financial.report.services', methods: ['GET', 'POST'])]
    public function generateServicePerformanceReport(Request $request): Response
    {
        $form = $this->createForm(SalesReportFormType::class);
        $form->handleRequest($request);

        $company = $this->getUser()->getCompany();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];

            return $this->redirect($this->generateUrl('dashboard.financial.report.services', [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]));
        }

        [ $startDate, $endDate ] = $this->financialReportService->getStartAndEndDate($request);

        [
            'services' => $services,
            'mostSoldService' => $mostSoldService,
            'highestRevenueService' => $highestRevenueService,
            'leastSoldService' => $leastSoldService,
            'lowestRevenueService' => $lowestRevenueService,
        ] = $this->financialReportService->generateServicePerformanceReport($startDate, $endDate, $company);

        $data = [
            'services' => $services,
            'mostSoldService' => $mostSoldService,
            'highestRevenueService' => $highestRevenueService,
            'leastSoldService' => $leastSoldService,
            'lowestRevenueService' => $lowestRevenueService,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y/m/d'),
            'form' => $form,
        ];

        return $this->render('dashboard/financial_report/services_report/index.html.twig', $data);
    }
    
}
