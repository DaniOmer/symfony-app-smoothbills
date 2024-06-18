<?php

namespace App\Entity;

use App\Repository\QuotationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuotationRepository::class)]
class Quotation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 45)]
    private ?string $uuid = null;

    /**
     * @var Collection<int, QuotationHasService>
     */
    #[ORM\OneToMany(mappedBy: 'quotation', targetEntity: QuotationHasService::class)]
    private Collection $quotationHasServices;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuotationStatus $quotation_status = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->quotationHasServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection<int, QuotationHasService>
     */
    public function getQuotationHasServices(): Collection
    {
        return $this->quotationHasServices;
    }

    public function addQuotationHasService(QuotationHasService $quotationHasService): static
    {
        if (!$this->quotationHasServices->contains($quotationHasService)) {
            $this->quotationHasServices->add($quotationHasService);
            $quotationHasService->setQuotation($this);
        }

        return $this;
    }

    public function removeQuotationHasService(QuotationHasService $quotationHasService): static
    {
        if ($this->quotationHasServices->removeElement($quotationHasService)) {
            // set the owning side to null (unless already changed)
            if ($quotationHasService->getQuotation() === $this) {
                $quotationHasService->setQuotation(null);
            }
        }

        return $this;
    }

    public function getQuotationStatus(): ?QuotationStatus
    {
        return $this->quotation_status;
    }

    public function setQuotationStatus(?QuotationStatus $quotation_status): static
    {
        $this->quotation_status = $quotation_status;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
