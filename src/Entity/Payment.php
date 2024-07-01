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

    #[ORM\Column(type: Types::STRING, length: 14, nullable: false)]
    private ?string $payment_number = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?float $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentMethod = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $stripeLastDigits = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class)]
    #[ORM\JoinColumn(name: "invoice_id", referencedColumnName: "id", nullable: false)]
    private ?Invoice $invoice = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?OneTimePayment $oneTimePayment = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?RecurringPayment $recurringPayment = null;

    public function __construct()
    {
        $this->UuidConstruct();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentNumber(): ?string
    {
        return $this->payment_number;
    }

    public function setPaymentNumber(string $payment_number): static
    {
        $this->payment_number = $payment_number;
        return $this;
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

    public function setStripePaymentMethod(?string $stripePaymentMethod): static
    {
        $this->stripePaymentMethod = $stripePaymentMethod;
        return $this;
    }

    public function getStripeLastDigits(): ?string
    {
        return $this->stripeLastDigits;
    }

    public function setStripeLastDigits(?string $stripeLastDigits): static
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

    public function getOneTimePayment(): ?OneTimePayment
    {
        return $this->oneTimePayment;
    }

    public function setOneTimePayment(?OneTimePayment $oneTimePayment): static
    {
        $this->oneTimePayment = $oneTimePayment;

        return $this;
    }

    public function getRecurringPayment(): ?RecurringPayment
    {
        return $this->recurringPayment;
    }

    public function setRecurringPayment(?RecurringPayment $recurringPayment): static
    {
        $this->recurringPayment = $recurringPayment;

        return $this;
    }
}
