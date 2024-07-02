<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    use TimestampableTrait;
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

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $sidebar_position = null;

    #[ORM\Column(length: 45)]
    private ?string $bg_color = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_default = null;

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

    public function getBgColor(): ?string
    {
        return $this->bg_color;
    }

    public function setBgColor(string $bg_color): static
    {
        $this->bg_color = $bg_color;

        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->is_default;
    }

    public function setDefault(?bool $is_default): static
    {
        $this->is_default = $is_default;

        return $this;
    }
}
