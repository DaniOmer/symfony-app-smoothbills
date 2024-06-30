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

    #[Route('/', name: 'dashboard.financial.report', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SalesReportFormType::class);
        $form->handleRequest($request);

        $company = $this->getUser()->getCompany();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startDate = $data['startDate'];
            $endDate = $data['endDate'];

            return $this->redirect($this->generateUrl('dashboard.financial.report', [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
            ]));
        }

        $startDateParam = $request->query->get('startDate');
        $endDateParam = $request->query->get('endDate');

        $startDate = $startDateParam ? new \DateTime($startDateParam) : new \DateTime('-30 days');
        $endDate = $endDateParam ? new \DateTime($endDateParam) : new \DateTime();

        $sales = $this->salesReportService->generateSalesReportByPeriod($startDate, $endDate, $company);

        $data = [
            'invoices' => $sales['invoices'],
            'totalAmountHT' => $sales['totalAmountHT'],
            'totalAmountTTC' => $sales['totalAmountTTC'],
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y/m/d'),
            'rawStartDate' => $startDate->format('d/m/Y'),
            'rawEndDate' => $endDate->format('d/m/Y'),
            'form' => $form->createView(),
        ];

        return $this->render('dashboard/financial_report/index.html.twig', $data);
    }
}
