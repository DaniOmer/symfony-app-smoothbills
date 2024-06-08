<?php

namespace App\Entity;

use App\Repository\CustomerSubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerSubscriptionRepository::class)]
class CustomerSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $starting_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expiration_date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartingDate(): ?\DateTimeInterface
    {
        return $this->starting_date;
    }

    public function setStartingDate(\DateTimeInterface $starting_date): static
    {
        $this->starting_date = $starting_date;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expiration_date;
    }

    public function setExpirationDate(\DateTimeInterface $expiration_date): static
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }
}
