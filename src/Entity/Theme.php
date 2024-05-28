<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    public const SIDEBAR_POSITION_LEFT = 'left';
    public const SIDEBAR_POSITION_RIGHT = 'right';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 45)]
    private ?string $primary_color = null;

    #[ORM\Column(length: 45)]
    private ?string $secondary_color = null;

    #[ORM\Column(length: 45)]
    private ?string $tertiary_color = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Font $title_font = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Font $content_font = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Font $subtitle_font = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $sidebar_position = null;

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

    public function getPrimaryColor(): ?string
    {
        return $this->primary_color;
    }

    public function setPrimaryColor(string $primary_color): static
    {
        $this->primary_color = $primary_color;

        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondary_color;
    }

    public function setSecondaryColor(string $secondary_color): static
    {
        $this->secondary_color = $secondary_color;

        return $this;
    }

    public function getTertiaryColor(): ?string
    {
        return $this->tertiary_color;
    }

    public function setTertiaryColor(string $tertiary_color): static
    {
        $this->tertiary_color = $tertiary_color;

        return $this;
    }

    public function getTitleFont(): ?Font
    {
        return $this->title_font;
    }

    public function setTitleFont(?Font $title_font): static
    {
        $this->title_font = $title_font;

        return $this;
    }

    public function getContentFont(): ?Font
    {
        return $this->content_font;
    }

    public function setContentFont(?Font $content_font): static
    {
        $this->content_font = $content_font;

        return $this;
    }

    public function getSubtitleFont(): ?Font
    {
        return $this->subtitle_font;
    }

    public function setSubtitleFont(?Font $subtitle_font): static
    {
        $this->subtitle_font = $subtitle_font;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSidebarPosition(): ?string
    {
        return $this->sidebar_position;
    }

    public function setSidebarPosition(string $sidebar_position): static
    {
        if (!in_array($sidebar_position, [self::SIDEBAR_POSITION_LEFT, self::SIDEBAR_POSITION_RIGHT])) {
            throw new \InvalidArgumentException("Invalid sidebar position");
        }
        $this->sidebar_position = $sidebar_position;
        return $this;
    }
}
