<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

class InvoiceRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Invoice::class);
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
    }

    public function getLastInvoiceNumberForCompany(int $companyId): ?int
    {
        $lastInvoice = $this->createQueryBuilder('i')
            ->select('i.invoice_number')
            ->where('i.company = :company')
            ->setParameter('company', $companyId)
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $lastInvoice ? (int) substr($lastInvoice['invoice_number'], -4) : null;
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

    public function paginateInvoicesByCompany(User $user, int $page): PaginationInterface
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
            'invoice_number' => $invoice->getInvoiceNumber(),
            'invoice_date' => $invoice->getCreatedAt()->format('d-m-Y'),
            'amount_ht' => $amountHt,
            'amount_ttc' => $amountTtc,
            'client' => $customerName,
        ];
    }

    public function findOverdueInvoices(\DateTime $date): array
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.invoiceStatus', 's')
            ->where('i.due_date < :date')
            ->andWhere('s.name != :paid')
            ->setParameter('date', $date)
            ->setParameter('paid', 'Paid')
            ->getQuery()
            ->getResult();
    }
}
