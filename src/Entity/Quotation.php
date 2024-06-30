<?php

namespace App\Entity;

use App\Repository\QuotationRepository;
use App\Trait\TimestampableTrait;
use App\Trait\UuidTypeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuotationRepository::class)]
class Quotation
{
    use UuidTypeTrait { __construct as private UuidConstruct;}
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de devis ne doit pas être vide.")]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le statut du devis ne doit pas être vide.")]
    private ?QuotationStatus $quotation_status = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'entreprise ne doit pas être vide.")]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le client ne doit pas être vide.")]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sending_date = null;

    /**
     * @var Collection<int, QuotationHasService>
     */
    #[ORM\OneToMany(mappedBy: 'quotation', targetEntity: QuotationHasService::class)]
    #[Assert\Count(
        min: 1,
        minMessage: 'Vous devez ajouter au moins un service.'
    )]
    #[Assert\Valid()]
    private Collection $quotationHasServices;

    public function __construct()
    {
        self::UuidConstruct();
        $this->quotationHasServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSendingDate(): ?\DateTimeInterface
    {
        return $this->sending_date;
    }

    public function setSendingDate(?\DateTimeInterface $sending_date): static
    {
        $this->sending_date = $sending_date;

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
}
