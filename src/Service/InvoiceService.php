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

class InvoiceService
{
    private $invoiceRepository;
    private $invoiceStatusRepository;
    private $entityManager;
    private $csvExporter;
    private $translator;

    public function __construct(InvoiceRepository $invoiceRepository, InvoiceStatusRepository $invoiceStatusRepository, CsvExporter $csvExporter, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceStatusRepository = $invoiceStatusRepository;
        $this->csvExporter = $csvExporter;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->invoiceStatusRepository = $invoiceStatusRepository;
    }

    public function createInvoice(Quotation $quotation): void
    {
        $this->entityManager->beginTransaction();

        try {
            $invoiceStatus = $this->entityManager->getRepository(InvoiceStatus::class)->findOneBy(['name' => 'pending']);
            $invoiceNumber = $this->generateInvoiceNumber();
            $company = $quotation->getCompany();
            $invoice = new Invoice();

            $invoice->setQuotation($quotation);
            $invoice->setCompany($company);
            $invoice->setInvoiceStatus($invoiceStatus);
            $invoice->setInvoiceNumber($invoiceNumber);

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (Exception |  OptimisticLockException $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    private function generateInvoiceNumber(): string
    {
        $lastInvoiceNumber = $this->invoiceRepository->getLastInvoiceNumber();

        $year = date('Y');
        $month = date('m');
        $nextInvoiceNumber = $lastInvoiceNumber + 1;

        return 'FA' . $year . $month . str_pad((string) $nextInvoiceNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getPaginatedInvoices(User $user, $page): PaginationInterface
    {
        $paginateInvoices = $this->invoiceRepository->paginateInvoicesByCompagny($user, $page);

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
                'amount_ht' => $amountHt,
                'amount_ttc' => $amountTtc,
                'status' => $this->translator->trans('invoice.status.' . $invoice->getInvoiceStatus()->getName()),
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
            'uid' => $quotation->getUid(),
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
}
