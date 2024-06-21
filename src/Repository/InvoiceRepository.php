<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;


class InvoiceRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;
    public function __construct(ManagerRegistry $registry,  PaginatorInterface $paginate)
    {
        parent::__construct($registry, Invoice::class);
        $this->paginator = $paginate;
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

    public function paginateInvoices(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('r'),
            $page,
            5
        );
    }
}
