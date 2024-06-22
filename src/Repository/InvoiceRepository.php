<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\User;

class InvoiceRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    private TranslatorInterface $translator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator, TranslatorInterface $translator)
    {
        parent::__construct($registry, Invoice::class);
        $this->paginator = $paginator;
        $this->translator = $translator;
    }

    public function getLastInvoiceNumber(): int
    {
        $lastInvoice = $this->createQueryBuilder('i')
            ->select('i.uuid')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($lastInvoice) {
            $lastInvoiceNumber = (int) substr($lastInvoice['uuid'], -4);
            return $lastInvoiceNumber + 1;
        }

        return 1;
    }

    public function countInvoicesByStatus(string $statusName, int $companyId): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->innerJoin('i.invoiceStatus', 's')
            ->where('s.name = :statusName')
            ->andWhere('i.company = :companyId')
            ->setParameter('statusName', $statusName)
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateInvoicesByCompagny(User $user, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('q')
            ->andWhere('q.company = :company')
            ->setParameter('company', $user->getCompany())
            ->orderBy('q.id', 'ASC')
            ->getQuery(),
        $page,
        5
        );
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

        return [
                'id' => $invoice->getId(),
                'uid' => $quotation->getUid(),
                'invoice_number' => $invoice->getUuid(),
                'invoice_date' => $invoice->getDate()->format('d-m-Y'),
                'amount_ht' => $amountHt,
                'amount_ttc' => $amountTtc,
                'client' => $customerName,
        ];
     }

}