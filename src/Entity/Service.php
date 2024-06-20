<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 45)]
    private ?string $estimated_duration = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceStatus $service_status = null;

    /**
     * @var Collection<int, QuotationHasService>
     */
    #[ORM\OneToMany(mappedBy: 'service', targetEntity: QuotationHasService::class)]
    private Collection $quotationHasServices;

    public function __construct()
    {
        $this->quotationHasServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getEstimatedDuration(): ?string
    {
        return $this->estimated_duration;
    }

    public function setEstimatedDuration(string $estimated_duration): static
    {
        $this->estimated_duration = $estimated_duration;

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

    public function getServiceStatus(): ?ServiceStatus
    {
        return $this->service_status;
    }

    public function setServiceStatus(?ServiceStatus $service_status): static
    {
        $this->service_status = $service_status;

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
            $quotationHasService->setService($this);
        }

        return $this;
    }

    public function removeQuotationHasService(QuotationHasService $quotationHasService): static
    {
        if ($this->quotationHasServices->removeElement($quotationHasService)) {
            // set the owning side to null (unless already changed)
            if ($quotationHasService->getService() === $this) {
                $quotationHasService->setService(null);
            }
        }

        return $this;
    }
}
