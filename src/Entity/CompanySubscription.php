<?php

namespace App\Entity;

use App\Repository\CompanySubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanySubscriptionRepository::class)]
#[ORM\Table(name: "company_subscription")]
class CompanySubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Subscription $subscription = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $trialEndDate = null;

    #[ORM\Column(length: 45)]
    private ?string $stripeStatus = null;

    #[ORM\Column(length: 255)]
    private ?string $stripePaymentMethod = null;

    #[ORM\Column(length: 4)]
    private ?string $stripeLastDigits = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSubscriptionId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getTrialEndDate(): ?\DateTimeInterface
    {
        return $this->trialEndDate;
    }

    public function setTrialEndDate(?\DateTimeInterface $trialEndDate): static
    {
        $this->trialEndDate = $trialEndDate;
        return $this;
    }

    public function getStripeStatus(): ?string
    {
        return $this->stripeStatus;
    }

    public function setStripeStatus(string $stripeStatus): static
    {
        $this->stripeStatus = $stripeStatus;
        return $this;
    }

    public function getStripePaymentMethod(): ?string
    {
        return $this->stripePaymentMethod;
    }

    public function setStripePaymentMethod(string $stripePaymentMethod): static
    {
        $this->stripePaymentMethod = $stripePaymentMethod;
        return $this;
    }

    public function getStripeLastDigits(): ?string
    {
        return $this->stripeLastDigits;
    }

    public function setStripeLastDigits(string $stripeLastDigits): static
    {
        $this->stripeLastDigits = $stripeLastDigits;
        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(?string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        return $this;
    }
}
