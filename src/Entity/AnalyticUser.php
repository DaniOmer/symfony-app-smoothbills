<?php

namespace App\Entity;

use App\Repository\AnalyticUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyticUserRepository::class)]
#[ORM\Table(name: "analytic_user")]
class AnalyticUser
{
    #[ORM\Id]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Analytic $analytic = null;

    #[ORM\Id]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $isActive = null;

    public function getAnalytic(): ?Analytic
    {
        return $this->analytic;
    }

    public function setAnalytic(?Analytic $analytic): static
    {
        $this->analytic = $analytic;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }
}
