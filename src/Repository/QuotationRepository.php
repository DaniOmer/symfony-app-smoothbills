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

    public function getQuotationDetails(Quotation $quotation): array
    {
        $quotationDetails = [];
        $totalPriceWithoutTax = 0;
        $totalPriceWithTax = 0;
    
        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $quantity = $quotationHasService->getQuantity();
            $priceWithoutTax = $quotationHasService->getPriceWithoutTax();
            $priceWithTax = $quotationHasService->getPriceWithTax();
    
            $quotationDetails[] = [
                'quotation' => $quotation,
                'priceWithoutTax' => $priceWithoutTax,
                'priceWithTax' => $priceWithTax,
                'date' => $quotationHasService->getDate(),
                'quantity' => $quotationHasService->getQuantity(),
                'serviceName' => $quotationHasService->getService()->getName(),
                'company' => $quotationHasService->getService()->getCompany()->getDenomination(),
            ];
    
            $totalPriceWithoutTax += $priceWithoutTax * $quantity;
            $totalPriceWithTax += $priceWithTax * $quantity;
        }
    
        return [
            'quotationDetails' => $quotationDetails,
            'totalPriceWithoutTax' => $totalPriceWithoutTax,
            'totalPriceWithTax' => $totalPriceWithTax,
        ];
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
