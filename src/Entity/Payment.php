<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use App\Trait\TimestampableTrait;
use App\Trait\UuidTypeTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: "payment")]
class Payment
{
    use UuidTypeTrait { __construct as private UuidConstruct;}
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $stripePaymentMethod = null;

    #[ORM\Column(length: 4)]
    private ?string $stripeLastDigits = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class)]
    #[ORM\JoinColumn(name: "invoice_id", referencedColumnName: "id", nullable: false)]
    private ?Invoice $invoice = null;

    public function __construct()
    {
        $this->UuidConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;
        return $this;
    }
}
