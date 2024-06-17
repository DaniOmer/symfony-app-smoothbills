<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginate)
    {
        parent::__construct($registry, Customer::class);
        $this->paginator = $paginate;
    }

    public function paginateCustomersByCompany(User $user, int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
                ->andWhere('c.company = :company')
                ->setParameter('company', $user->getCompany())
                ->orderBy('c.id', 'ASC')
                ->getQuery(),
            $page,
            5
        );
    }

    //    /**
    //     * @return Customer[] Returns an array of Customer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Customer
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
