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

    public function findInvoicesByPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate, $company): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.created_at BETWEEN :startDate AND :endDate')
            ->andWhere('i.company = :company')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    public function getMostlySalesServices(\DateTimeInterface $startDate, \DateTimeInterface $endDate, $company)
    {
        return $this->createQueryBuilder('i')
            ->select('s.uid, s.name, s.price, SUM(qhs.price_without_tax * qhs.quantity) as revenueHT, SUM(qhs.price_with_tax * qhs.quantity) as revenueTTC, SUM(qhs.quantity) AS quantity')
            ->join('i.quotation', 'q')
            ->join('q.quotationHasServices', 'qhs')
            ->join('qhs.service', 's')
            ->where('i.company = :company')
            ->andWhere('i.created_at BETWEEN :startDate AND :endDate')
            ->groupBy('s.id')
            ->orderBy('quantity', 'DESC')
            ->setParameter('company', $company)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
