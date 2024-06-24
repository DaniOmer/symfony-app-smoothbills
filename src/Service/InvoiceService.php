<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceStatusRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceService
{
    private $invoiceRepository;
    private InvoiceStatusRepository $invoiceStatusRepository;
 
    private $csvExporter;
    private $translator;

    public function __construct(InvoiceRepository $invoiceRepository,  InvoiceStatusRepository $invoiceStatusRepository, CsvExporter $csvExporter, TranslatorInterface $translator)
    {
        $this->invoiceRepository = $invoiceRepository; 
        $this->invoiceStatusRepository = $invoiceStatusRepository;
        $this->csvExporter = $csvExporter;
        $this->translator = $translator;
    }

    public function getPaginatedInvoices(User $user, $page): PaginationInterface
    {
        $paginateInvoices = $this->invoiceRepository->paginateInvoicesByCompagny($user, $page);

        return $paginateInvoices;
    }
   

    public function getInvoicesRows(User $user, $page): Array
    {

        $rows=[];
        foreach ($this->getPaginatedInvoices($user, $page) as $invoice) { 
            $quotation = $invoice->getQuotation();
            $customerName = $quotation->getCustomer()->getName();
            
            $amountHt = 0;
            $amountTtc = 0;
            
            foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
                
                $amountHt += $quotationHasService->getPriceWithoutTax() * $quotationHasService->getQuantity();
                $amountTtc += $quotationHasService->getPriceWithTax() * $quotationHasService->getQuantity();
            }

            $rows[]= [
                'id' => $invoice->getId(),
                'uid' => $invoice->getUid(),
                'invoice_number' => $invoice->getUid(),
                'invoice_date' => $invoice->getCreatedAt()->format('d-m-Y'),
                'amount_ht'=> $amountHt,
                'amount_ttc' => $amountTtc,
                'status' => $this->translator->trans('invoice.status.' . $invoice->getInvoiceStatus()->getName()),
                'client' => $customerName ,
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


