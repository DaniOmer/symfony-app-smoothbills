<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
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
            $lastInvoiceNumber = (int)substr($lastInvoice['uuid'], -4);
            return $lastInvoiceNumber + 1;
        }

        return 1;
    }
}
