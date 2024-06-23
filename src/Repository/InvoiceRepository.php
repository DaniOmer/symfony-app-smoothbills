<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\InvoiceStatusRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

class InvoiceRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;
    private InvoiceStatusRepository $invoiceStatusRepository;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator, EntityManagerInterface $entityManager, InvoiceStatusRepository $invoiceStatusRepository)
    {
        parent::__construct($registry, Invoice::class);
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
        $this->invoiceStatusRepository = $invoiceStatusRepository;
    }

    public function getLastInvoiceNumber(): int
    {
        $lastInvoice = $this->createQueryBuilder('i')
            ->select('i.invoice_number')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($lastInvoice) {
            $lastInvoiceNumber = (int) substr($lastInvoice['invoice_number'], -4);
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

    public function getAllInvoiceStatusNames(): array
    {
        $invoiceStatuses = $this->invoiceStatusRepository->findAll();

        $statusNames = [];

        foreach ($invoiceStatuses as $status) {
            $statusNames[$status->getName()] = $status->getName();
        }

        return $statusNames;
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
            'invoice_number' => $invoice->getInvoiceNumber(),
            'invoice_date' => $invoice->getCreatedAt()->format('d-m-Y'),
            'amount_ht' => $amountHt,
            'amount_ttc' => $amountTtc,
            'client' => $customerName,
        ];
    }
}