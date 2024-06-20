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

    public function countTotalServices(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('count(s.id)')
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
}