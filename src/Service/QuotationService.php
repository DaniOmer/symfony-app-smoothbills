<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\QuotationRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class QuotationService
{
    private $quotationRepository;

    public function __construct(QuotationRepository $quotationRepository)
    {
        $this->quotationRepository = $quotationRepository;
    }

    public function getPaginatedQuotations($page): PaginationInterface
    {
        $paginateQuotations = $this->quotationRepository->paginateQuotations($page);

        return $paginateQuotations;
    }

    public function getQuotationsRows($page): Array
    {
        $rows = [];

        foreach ($this->getPaginatedQuotations($page) as $quotation) {
            $rows[] = [
                'id' => $quotation->getId(),
                'uid' => $quotation->getUid(),
                'name' => $quotation->getUid(),
                'status' => $quotation->getQuotationStatus()->getName(),
                'client' => $quotation->getCustomer()->getName(),
                'date' => $quotation->getDate()->format('Y-m-d H:i:s'),
            ];
        }

        return $rows;
    }
}