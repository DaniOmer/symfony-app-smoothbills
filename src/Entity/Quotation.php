<?php

namespace App\Entity;

use App\Repository\QuotationRepository;
use App\Trait\TimestampableTrait;
use App\Trait\UuidTypeTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuotationStatus $quotation_status = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'quotations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sending_date = null;

    public function __construct()
    {
        self::UuidConstruct();
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
}
