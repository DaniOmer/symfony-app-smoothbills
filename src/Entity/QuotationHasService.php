<?php

namespace App\Entity;

use App\Repository\QuotationHasServiceRepository;
use App\Trait\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuotationHasServiceRepository::class)]
class QuotationHasService
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_without_tax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_with_tax = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité ne doit pas être vide.')]
    #[Assert\Positive(message: 'La quantité doit être un nombre positif.')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9\s\-]+$/',
        message: 'La quantité saisie n\'est pas valide.'
    )]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'quotationHasServices')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le service ne doit pas être vide.")]
    private ?Service $service = null;


    #[ORM\ManyToOne(inversedBy: 'quotationHasServices')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Le devis ne doit pas être vide.")]
    private ?Quotation $quotation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriceWithoutTax(): ?string
    {
        return $this->price_without_tax;
    }

    public function setPriceWithoutTax(string $price_without_tax): static
    {
        $this->price_without_tax = $price_without_tax;

        return $this;
    }

    public function getPriceWithTax(): ?string
    {
        return $this->price_with_tax;
    }

    public function setPriceWithTax(string $price_with_tax): static
    {
        $this->price_with_tax = $price_with_tax;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

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
}