<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\InvoiceStatus;
use App\Entity\Quotation;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceStatusRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use App\Service\PdfGeneratorService;
use App\Utils\NumberGenerator;

class InvoiceService
{
    private $invoiceRepository;
    private $invoiceStatusRepository;
    private $entityManager;
    private $pdfGeneratorService;
    private $translator;
    private $twig;
    private $mailer;
    private $adminEmail;
    private $numberGenerator;

    public function __construct(
        Environment $twig,
        InvoiceRepository $invoiceRepository,
        InvoiceStatusRepository $invoiceStatusRepository,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        PdfGeneratorService $pdfGeneratorService,
        MailerInterface $mailer,
        #[Autowire('%admin_email%')] string $adminEmail,
        NumberGenerator $numberGenerator,
    ) {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceStatusRepository = $invoiceStatusRepository;
        $this->pdfGeneratorService = $pdfGeneratorService;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->numberGenerator = $numberGenerator;
    }

    public function createInvoice(Quotation $quotation): Invoice
    {
        $this->entityManager->beginTransaction();

        try {
            $invoiceStatus = $this->entityManager->getRepository(InvoiceStatus::class)->findOneBy(['name' => 'Pending']);
            $company = $quotation->getCompany();
            $invoiceNumber = $this->generateInvoiceNumber($company->getId());

            $invoice = new Invoice();
            $invoice->setQuotation($quotation);
            $invoice->setCompany($company);
            $invoice->setInvoiceStatus($invoiceStatus);
            $invoice->setInvoiceNumber($invoiceNumber);
            $invoice->setDueDate((new \DateTime())->modify('+30 days'));

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $invoice;
        } catch (Exception | OptimisticLockException $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function generateInvoiceNumber(int $companyId): string
    {
        $prefix = 'FA';
        $lastInvoiceNumber = $this->invoiceRepository->getLastInvoiceNumberForCompany($companyId);

        $invoiceNumber = $this->numberGenerator->generateDocumentNumber($lastInvoiceNumber, $prefix);

        return $invoiceNumber;
    }

    public function getPaginatedInvoices(User $user, $page): PaginationInterface
    {
        $paginateInvoices = $this->invoiceRepository->paginateInvoicesByCompany($user, $page);

        return $paginateInvoices;
    }

    public function getInvoicesRows(User $user, $page): array
    {
        $rows = [];
        foreach ($this->getPaginatedInvoices($user, $page) as $invoice) {
            $quotation = $invoice->getQuotation();
            $customerName = $quotation->getCustomer()->getName();

            $amountHt = 0;
            $amountTtc = 0;

            foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
                $amountHt += $quotationHasService->getPriceWithoutTax() * $quotationHasService->getQuantity();
                $amountTtc += $quotationHasService->getPriceWithTax() * $quotationHasService->getQuantity();
            }

            $rows[] = [
                'id' => $invoice->getId(),
                'uid' => $invoice->getUid(),
                'invoice_number' => $invoice->getInvoiceNumber(),
                'invoice_date' => $invoice->getCreatedAt()->format('d-m-Y'),
                'due_date' => $invoice->getDueDate()->format('d-m-Y'),
                'amount_ht' => $amountHt,
                'amount_ttc' => $amountTtc,
                'status' =>  $invoice->getInvoiceStatus()->getName(),
                'client' => $customerName,
            ];
        }

        return $rows;
    }

    public function getInvoiceDetails(Invoice $invoice): ?array
    {
        $quotation = $invoice->getQuotation();
        $customerName = $quotation->getCustomer()->getName();

        $amountHt = 0;
        $amountTtc = 0;

        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $amountHt += $quotationHasService->getPriceWithoutTax() * $quotationHasService->getQuantity();
            $amountTtc += $quotationHasService->getPriceWithTax() * $quotationHasService->getQuantity();
        }

        $invoiceDetails = [
            'id' => $invoice->getId(),
            'uid' => $invoice->getUid(),
            'invoice_number' => $invoice->getInvoiceNumber(),
            'invoice_date' => $invoice->getCreatedAt()->format('d-m-Y'),
            'amount_ht' => $amountHt,
            'amount_ttc' => $amountTtc,
            'client' => $customerName,
        ];

        return $invoiceDetails;
    }

    public function getAllInvoiceStatusNames(): array
    {
        $invoiceStatuses = $this->invoiceStatusRepository->findAll();
        $statusNames = [];

        foreach ($invoiceStatuses as $status) {
            $statusNames[$status->getName()] = $status->getName();
        }

        return $statusNames;
    }

    public function getInvoiceDataForPdf(Invoice $invoice): array
    {
        $quotation = $invoice->getQuotation();
        $company = $invoice->getCompany();
        $companyAddress = $company->getAddress();
        $customer = $quotation->getCustomer();
        $customerAddress = $customer->getAddress();
        $companyCustomer = $customer->getCompany();
        $services = $quotation->getQuotationHasServices();

        $data = [
            'invoice' => [
                'invoice_number' => $invoice->getInvoiceNumber(),
                'sending_date' => $quotation->getSendingDate(),
            ],
            'company' => [
                'name' => $company->getDenomination(),
                'address' => $company->getAddress(),
                'zip_code' => $companyAddress->getZipCode(),
                'city' => $companyAddress->getCity(),
                'address' => $companyAddress->getAddress(),
                'country' => $companyAddress->getCountry(),
                'vat_number' => $company->getTvaNumber(),
                'phone' => $company->getPhoneNumber(),
                'email' => $company->getMail(),
                'siret' => $company->getSiret(),
                'logo' => $company->getLogo(),
                'siren' => $company->getSiren(),
            ],
            'customer' => [
                'name' => $customer->getName(),
                'code' => $customer->getUid(),
                'address' => $customerAddress->getAddress(),
                'zip_code' => $customerAddress->getZipCode(),
                'city' => $customerAddress->getCity(),
                'country' => $customerAddress->getCountry(),
                'phone' => $customer->getPhone(),
                'email' => $customer->getMail(),
                'company' => $companyCustomer->getDenomination(),
                'vat_number' => $companyCustomer->getTvaNumber(),
                'siret' => $companyCustomer->getSiret(),
                'siren' => $companyCustomer->getSiren(),
            ],
            'services' => [],
        ];

        foreach ($services as $service) {
            $data['services'][] = [
                'name' => $service->getService()->getName(),
                'quantity' => $service->getQuantity(),
                'price_without_tax' => $service->getPriceWithoutTax(),
                'price_with_tax' => $service->getPriceWithTax(),
            ];
        }

        return $data;
    }

    public function sendInvoiceByEmail(Invoice $invoice): void
    {
        $company = $invoice->getCompany();
        $customer = $invoice->getQuotation()->getCustomer();

        $data = $this->getInvoiceDataForPdf($invoice);
        $twigTemplate = $this->twig->render('dashboard/invoice/pdf/invoice_template.html.twig', $data);
        $filename = 'invoice_' . $invoice->getInvoiceNumber() . '.pdf';

        $invoicePdf = $this->pdfGeneratorService->getPdfBinaryContent($twigTemplate);

        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmail, $company->getDenomination()))
            ->to($customer->getMail())
            ->subject('Nouvelle facture créé')
            ->html('<h1>Nouvelle facture crée</h1><p>Merci pour votre confiance</p>')
            ->attach($invoicePdf, $filename, 'application/pdf');

        $this->mailer->send($email);
    }

    public function sendInvoiceReminder(Invoice $invoice): void
    {
        $company = $invoice->getCompany();
        $customer = $invoice->getQuotation()->getCustomer();

        $data = $this->getInvoiceDataForPdf($invoice);
        $twigTemplate = $this->twig->render('dashboard/invoice/pdf/invoice_template.html.twig', $data);
        $filename = 'invoice_' . $invoice->getInvoiceNumber() . '.pdf';
        $invoicePdf = $this->pdfGeneratorService->getPdfBinaryContent($twigTemplate);

        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmail, $company->getDenomination()))
            ->to($customer->getMail())
            ->subject('Rappel de facture')
            ->attach($invoicePdf, $filename, 'application/pdf')
            ->context([
                'invoice' => [
                    'invoice_number' => $invoice->getInvoiceNumber(),
                    'sending_date' => $invoice->getCreatedAt()->format('d-m-Y'),
                ],
                'company' => [
                    'name' => $company->getDenomination()
                ],
                'customer' => [
                    'name' => $customer->getName()
                ]
            ])
            ->htmlTemplate('dashboard/invoice/mail/invoice_reminder.html.twig');


        $this->mailer->send($email);
    }
}