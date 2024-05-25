<?php

namespace App\Repository;

use App\Entity\LegalForm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LegalForm>
 *
 * @method LegalForm|null find($id, $lockMode = null, $lockVersion = null)
 * @method LegalForm|null findOneBy(array $criteria, array $orderBy = null)
 * @method LegalForm[]    findAll()
 * @method LegalForm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LegalFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LegalForm::class);
    }

//    /**
//     * @return LegalForm[] Returns an array of LegalForm objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LegalForm
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
