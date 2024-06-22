<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;



class InvoiceService
{
    private $invoiceRepository;
 
    private $csvExporter;
    private $translator;

    public function __construct(InvoiceRepository $invoiceRepository, CsvExporter $csvExporter, TranslatorInterface $translator)
    {
        $this->invoiceRepository = $invoiceRepository; 
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


   

   
}


