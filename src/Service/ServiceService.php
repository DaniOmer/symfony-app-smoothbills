<?php

namespace App\Service;

use App\Entity\Service;
use App\Entity\User;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormInterface;

class ServiceService
{
    private $serviceRepository;
    private $entityManager;

    public function __construct(ServiceRepository $serviceRepository, EntityManagerInterface $entityManager)
    {
        $this->serviceRepository = $serviceRepository;
        $this->entityManager = $entityManager;
    }

    public function getPaginatedServices(User $user, int $page): PaginationInterface
    {
        return $this->serviceRepository->paginateServicesByCompany($user, $page);
    }

    public function getServicesRows(User $user, int $page): array
    {
        $rows = [];

        foreach ($this->getPaginatedServices($user, $page) as $service) {
            $rows[] = [
                'id' => $service->getId(),
                'uid' => $service->getUid(),
                'name' => $service->getName(),
                'price' => $service->getPrice(),
                'estimated_duration' => $service->getEstimatedDuration(),
                'status' => $service->getServiceStatus()->getName(),
            ];
        }

        return $rows;
    }


    public function createService(FormInterface $form, Service $service, User $user): void
    {
        $service->setCompany($user->getCompany());
        $this->entityManager->persist($service);
        $this->entityManager->flush();
    }
}