<?php

namespace App\Repository;

use App\Entity\Quotation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Quotation>
 */
class QuotationRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginate)
    {
        parent::__construct($registry, Quotation::class);
        $this->paginator = $paginate;
    }

    public function paginateQuotationsByCompany(User $user, int $page): PaginationInterface
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

    public function countTotalQuotationsByCompany(User $user): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.company = :company')
            ->setParameter('company', $user->getCompany())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countQuotationsByStatus(string $statusName, int $companyId): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->innerJoin('q.quotation_status', 's')
            ->where('s.name = :statusName')
            ->andWhere('q.company = :companyId')
            ->setParameter('statusName', $statusName)
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countQuotationsForCompanyThisMonth($companyId): int
    {
        $qb = $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->andWhere('q.company = :company')
            ->andWhere('q.sending_date BETWEEN :start AND :end')
            ->setParameter('company', $companyId)
            ->setParameter('start', new \DateTime('first day of this month'))
            ->setParameter('end', new \DateTime('last day of this month'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getLastQuotationNumberForCompany(int $companyId): ?int
    {
        $lastQuotation = $this->createQueryBuilder('q')
            ->select('q.quotation_number')
            ->where('q.company = :company')
            ->setParameter('company', $companyId)
            ->orderBy('q.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $lastQuotation ? (int) substr($lastQuotation['quotation_number'], -4) : null;
    }

    //    /**
    //     * @return Quotation[] Returns an array of Quotation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Quotation
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}