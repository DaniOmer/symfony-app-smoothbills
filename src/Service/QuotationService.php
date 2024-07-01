<?php

namespace App\Service;

use App\Entity\GraphicChart;
use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use App\Entity\User;
use App\Repository\QuotationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class QuotationService
{
    private $entityManager;
    private $quotationRepository;
    private $invoiceService;
    private $mailer;
    private $adminEmail;
    private $csvExporter;
    private $taxService;
    private $jWTService;
    private $pdfGeneratorService;
    private $twig;
    private $paymentService;

    public function __construct(
        Environment $twig,
        EntityManagerInterface $entityManager,
        QuotationRepository $quotationRepository,
        InvoiceService $invoiceService,
        MailerInterface $mailer,
        #[Autowire('%admin_email%')] string $adminEmail,
        CsvExporter $csvExporter,
        TaxService $taxService,
        JWTService $jWTService,
        PdfGeneratorService $pdfGeneratorService,
        PaymentService $paymentService,
    ) {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->quotationRepository = $quotationRepository;
        $this->invoiceService = $invoiceService;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
        $this->taxService = $taxService;
        $this->jWTService = $jWTService;
        $this->pdfGeneratorService = $pdfGeneratorService;
        $this->paymentService = $paymentService;
    }

    public function getPaginatedQuotations(User $user, $page): PaginationInterface
    {
        $paginateQuotations = $this->quotationRepository->paginateQuotationsByCompany($user, $page);

        return $paginateQuotations;
    }

    public function getQuotationsRows(User $user, $page): array
    {
        $rows = [];

        foreach ($this->getPaginatedQuotations($user, $page) as $quotation) {
            $rows[] = [
                'id' => $quotation->getId(),
                'uid' => $quotation->getUid(),
                'name' => $quotation->getUid(),
                'status' => $quotation->getQuotationStatus()->getName(),
                'client' => $quotation->getCustomer()->getName(),
                'sendingDate' => $quotation->getSendingDate() ? $quotation->getSendingDate()->format('Y-m-d H:i:s') : '',
            ];
        }

        return $rows;
    }

    public function sendQuotationMail(Quotation $quotation, $validationUrl): void
    {
        $company = $quotation->getCompany();
        $customer = $quotation->getCustomer();

        $data = $this->getQuotationDataForPdf($quotation);
        $twigTemplate = $this->twig->render('dashboard/quotation/pdf/quotation_template.html.twig', $data);
        $filename = 'quotation_' . $quotation->getUid() . '.pdf';

        $invoicePdf = $this->pdfGeneratorService->getPdfBinaryContent($twigTemplate);

        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmail, $company->getDenomination()))
            ->to($quotation->getCustomer()->getMail())
            ->subject('Nouveau devis créé')
            ->htmlTemplate('dashboard/quotation/mail/quotation_email.html.twig')
            ->context([
                'quotation' => $quotation,
                'company' => $company,
                'customerName' => $customer->getName(),
                'validationUrl' => $validationUrl,
            ])
            ->attach($invoicePdf, $filename, 'application/pdf');

        $this->mailer->send($email);
    }

    public function getQuotationDataForPdf(Quotation $quotation): array
    {
        $sendingDate = $quotation->getSendingDate();
        $quotationStatus = $quotation->getQuotationStatus()->getName();
        $quotationDetails = $this->getQuotationDetails($quotation);
        $validityDate = $this->getQuotationValidityDate($sendingDate);

        $data = [
            'quotation' => $quotation,
            'validityDate' => $validityDate,
            'quotationDetails' => $quotationDetails['quotationDetails'],
            'quotationStatus' => $quotationStatus,
            'totalPriceWithoutTax' => $quotationDetails['totalPriceWithoutTax'],
            'totalPriceWithTax' => $quotationDetails['totalPriceWithTax'],
            'graphicChart' => $quotationDetails['graphicChart'],
        ];

        return $data;
    }

    public function generateQuotationValidationToken(Quotation $quotation)
    {
        $expiry = new \DateTime('+30 days');
        $quotationUid = $quotation->getUid();

        return $this->jWTService->createToken(['quotation_uid' => $quotationUid, 'exp' => $expiry->getTimestamp()], $expiry->getTimestamp());
    }

    public function exportAllQuotations(): Response
    {
        $quotations = $this->quotationRepository->findAll();
        $headers = ['N° de Devis', 'Status', 'Client', 'Envoyé le'];
        $dataExtractor = function(Quotation $quotation) {
            return [
                $quotation->getUid(),
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getSendingDate() ? $quotation->getSendingDate()->format('Y-m-d H:i:s') : '',
            ];
        };

        return $this->csvExporter->exportEntities($quotations, $headers, $dataExtractor, 'all_quotations');
    }

    public function getQuotationDetails(Quotation $quotation): array
    {
        $quotationDetails = [];
        $totalPriceWithoutTax = 0;
        $totalPriceWithTax = 0;
        $company = $quotation->getCompany();
        $graphicChart = $this->entityManager->getRepository(GraphicChart::class)->findOneBy(['company' => $company]);

        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $quantity = $quotationHasService->getQuantity();
            $priceWithoutTax = $quotationHasService->getPriceWithoutTax();
            $priceWithTax = $quotationHasService->getPriceWithTax();

            $quotationDetails[] = [
                'quotation' => $quotation,
                'priceWithoutTax' => $priceWithoutTax,
                'priceWithTax' => $priceWithTax,
                'quantity' => $quotationHasService->getQuantity(),
                'serviceName' => $quotationHasService->getService()->getName(),
                'company' => $quotationHasService->getService()->getCompany()->getDenomination(),
            ];

            $totalPriceWithoutTax += $priceWithoutTax * $quantity;
            $totalPriceWithTax += $priceWithTax * $quantity;
        }

        return [
            'quotationDetails' => $quotationDetails,
            'totalPriceWithoutTax' => $totalPriceWithoutTax,
            'totalPriceWithTax' => $totalPriceWithTax,
            'graphicChart' => $graphicChart,
        ];
    }

    public function processQuotation(Quotation $quotation, $form, $company): void
    {
        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $priceWithoutTax = $quotationHasService->getService()->getPrice();
            $priceWithTax = $this->taxService->applyTva($priceWithoutTax);

            $quotationHasService->setPriceWithoutTax($priceWithoutTax);
            $quotationHasService->setPriceWithTax($priceWithTax);
            $this->entityManager->persist($quotationHasService);
        }

        $sendOption = $form->get('sendOption')->getData();
        if ($sendOption === 'Maintenant') {
            $quotation->setSendingDate(new \DateTime());
        } else {
            $quotation->setSendingDate(null);
        }

        $quotation->setCompany($company);

        $this->entityManager->persist($quotation);
        $this->entityManager->flush();

        $this->createInvoiceFromQuotation($quotation);
    }

    public function createInvoiceFromQuotation($quotation): void
    {
        $quotationStatus = $quotation->getQuotationStatus()->getName();

        if ($quotationStatus === 'Accepted') {
            $invoice = $this->invoiceService->createInvoice($quotation);
            
            if($invoice){
                $this->paymentService->createPayment($invoice);
            }
        }
    }

    public function validateQuotation(Quotation $quotation, $status): void
    {
        $quotationStatus = $this->entityManager->getRepository(QuotationStatus::class)->findOneBy(['name' => $status]);
        $quotation->setQuotationStatus($quotationStatus);

        $this->entityManager->persist($quotation);
        $this->entityManager->flush();
    }

    public function getQuotationTotalCount(User $user): int
    {
        return $this->quotationRepository->countTotalQuotationsByCompany($user);
    }

    public function getQuotationAcceptedCount(User $user): int
    {
        $company = $user->getCompany();
        $companyId = $company->getId();

        return $this->quotationRepository->countQuotationsByStatus('Accepted', $companyId);
    }

    public function getQuotationRejectedCount(User $user): int
    {
        $company = $user->getCompany();
        $companyId = $company->getId();

        return $this->quotationRepository->countQuotationsByStatus('Refused', $companyId);;
    }

    public function getConversionRate(User $user): float
    {
        $quotationTotal = $this->getQuotationTotalCount($user);
        $quotationAccepted = $this->getQuotationAcceptedCount($user);

        if ($quotationTotal === 0) {
            return 0.00;
        }

        $conversionRate = ($quotationAccepted / $quotationTotal) * 100;

        return round($conversionRate, 2);
    }

    public function getQuotationValidityDate($sendingDate): DateTime | String
    {
        if ($sendingDate === null) {
            return "Non définis";
        }
        return (clone $sendingDate)->modify('+30 days');
    }
}
