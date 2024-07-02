<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;
use App\Trait\UuidTypeTrait;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(name: "invoice")]
class Invoice
{
    use UuidTypeTrait {
        __construct as private UuidConstruct;
    }
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 14, nullable: false)]
    private ?string $invoice_number = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?InvoiceStatus $invoiceStatus = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Quotation $quotation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $due_date = null;

    public function __construct()
    {
        $this->UuidConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoice_number;
    }

    public function setInvoiceNumber(string $invoice_number): static
    {
        $this->invoice_number = $invoice_number;
        return $this;
    }

    public function getInvoiceStatus(): ?InvoiceStatus
    {
        return $this->invoiceStatus;
    }

    public function setInvoiceStatus(?InvoiceStatus $invoiceStatus): static
    {
        $this->invoiceStatus = $invoiceStatus;
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

    public function getQuotation(): ?Quotation
    {
        return $this->quotation;
    }

    public function setQuotation(?Quotation $quotation): static
    {
        $this->quotation = $quotation;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->due_date;
    }

    public function setDueDate(\DateTimeInterface $due_date): static
    {
        $this->due_date = $due_date;
        return $this;
    }
}