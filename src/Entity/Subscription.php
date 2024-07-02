<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\OneToMany(mappedBy: 'subscription', targetEntity: SubscriptionOption::class)]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return Collection<int, SubscriptionOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(SubscriptionOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setSubscription($this);
        }

        return $this;
    }

    public function removeOption(SubscriptionOption $option): static
    {
        if ($this->options->removeElement($option)) {
            if ($option->getSubscription() === $this) {
                $option->setSubscription(null);
            }
        }

        return $this;
    }
}
