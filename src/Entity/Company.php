<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use App\Trait\TimestampableTrait;
use App\Trait\UuidTypeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    use UuidTypeTrait {
        __construct as private UuidConstruct;
    }
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $denomination = null;

    #[ORM\Column(length: 255)]
    private ?string $siren = null;

    #[ORM\Column(length: 255)]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $tva_number = null;

    #[ORM\Column(length: 255)]
    private ?string $rcs_number = null;

    #[ORM\Column(length: 65)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 255)]
    private ?string $mail = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $creation_date = null;

    #[ORM\Column]
    private ?int $registered_social = null;

    #[ORM\Column(length: 255)]
    private ?string $sector = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signing = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    private ?LegalForm $legal_form = null;

    #[ORM\OneToOne(mappedBy: 'company', cascade: ['persist', 'remove'])]
    private ?CompanySubscription $subscription = null;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $address = null;

    /**
     * @var Collection<int, Quotation>
     */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Quotation::class)]
    private Collection $quotations;

    public function __construct()
    {
        $this->UuidConstruct();
        $this->customers = new ArrayCollection();
        $this->quotations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(string $denomination): static
    {
        $this->denomination = $denomination;

        return $this;
    }

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(string $siren): static
    {
        $this->siren = $siren;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getTvaNumber(): ?string
    {
        return $this->tva_number;
    }

    public function setTvaNumber(string $tva_number): static
    {
        $this->tva_number = $tva_number;

        return $this;
    }

    public function getRcsNumber(): ?string
    {
        return $this->rcs_number;
    }

    public function setRcsNumber(string $rcs_number): static
    {
        $this->rcs_number = $rcs_number;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): static
    {
        $this->creation_date = $creation_date;

        return $this;
    }

    public function getRegisteredSocial(): ?int
    {
        return $this->registered_social;
    }

    public function setRegisteredSocial(int $registered_social): static
    {
        $this->registered_social = $registered_social;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(string $sector): static
    {
        $this->sector = $sector;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSigning(): ?string
    {
        return $this->signing;
    }

    public function setSigning(?string $signing): static
    {
        $this->signing = $signing;

        return $this;
    }

    public function getLegalForm(): ?LegalForm
    {
        return $this->legal_form;
    }

    public function setLegalForm(?LegalForm $legal_form): static
    {
        $this->legal_form = $legal_form;

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCompany($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCompany() === $this) {
                $customer->setCompany(null);
            }
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Quotation>
     */
    public function getQuotations(): Collection
    {
        return $this->quotations;
    }

    public function addQuotation(Quotation $quotation): static
    {
        if (!$this->quotations->contains($quotation)) {
            $this->quotations->add($quotation);
            $quotation->setCompany($this);
        }

        return $this;
    }

    public function removeQuotation(Quotation $quotation): static
    {
        if ($this->quotations->removeElement($quotation)) {
            // set the owning side to null (unless already changed)
            if ($quotation->getCompany() === $this) {
                $quotation->setCompany(null);
            }
        }

        return $this;
    }

    public function getSubscription(): ?CompanySubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?CompanySubscription $subscription): static
    {
        $this->subscription = $subscription;
        if ($subscription !== null && $subscription->getCompany() !== $this) {
            $subscription->setCompany($this);
        }
        return $this;
    }
}
