<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceStatusRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;




class InvoiceService
{
    private $invoiceRepository;
    private $entityManager;
    private $csvExporter;
    private $translator;
    private $invoiceStatusRepository;

    public function __construct(InvoiceRepository $invoiceRepository, CsvExporter $csvExporter, TranslatorInterface $translator, EntityManagerInterface $entityManager, InvoiceStatusRepository $invoiceStatusRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->csvExporter = $csvExporter;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->invoiceStatusRepository = $invoiceStatusRepository;
    }

    public function createInvoice(Invoice $invoice): void
    {
        $this->entityManager->beginTransaction();

        try {
            $year = date('Y');
            $month = date('m');
            $lastInvoiceNumber = $this->invoiceRepository->getLastInvoiceNumber();
            $lastInvoiceNumber = str_pad((string)$lastInvoiceNumber, 4, '0', STR_PAD_LEFT);

            $lastInvoiceNumber = 'FA' . $year . $month . $lastInvoiceNumber;
            $invoice->setInvoiceNumber($lastInvoiceNumber);

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception |  OptimisticLockException $e) {
            $this->entityManager->rollback();
            throw $e;
        }
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
            'invoice_number' => $invoice->getUid(),
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
}
