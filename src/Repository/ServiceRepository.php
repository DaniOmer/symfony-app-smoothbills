<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Service::class);
        $this->paginator = $paginator;
    }

    public function paginateServicesByCompany(User $user, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('s')
                ->andWhere('s.company = :company')
                ->setParameter('company', $user->getCompany())
                ->orderBy('s.id', 'ASC')
                ->getQuery(),
            $page,
            5
        );
    }

    public function countTotalServicesByCompany(User $user): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.company = :company')
            ->setParameter('company', $user->getCompany())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countServicesByStatus(string $status, int $companyId): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->join('s.service_status', 'ss')
            ->where('ss.name = :status')
            ->andWhere('s.company = :companyId')
            ->setParameter('status', $status)
            ->setParameter('companyId', $companyId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByUid($uid): ?Service
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.uid = :uid')
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTop3ServicesBySales(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.name AS title, COUNT(qhs.id) AS sales, SUM(qhs.price_with_tax * qhs.quantity) AS revenue')
            ->join('s.quotationHasServices', 'qhs')
            ->groupBy('s.id')
            ->orderBy('sales', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function getTop3TransactionsByHighestPrice(): array
    {
        return $this->createQueryBuilder('s')
            ->select('q.id , s.name AS service, qhs.created_at, qhs.price_with_tax AS price')
            ->join('s.quotationHasServices', 'qhs')
            ->join('qhs.quotation', 'q')
            ->orderBy('qhs.price_with_tax', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
