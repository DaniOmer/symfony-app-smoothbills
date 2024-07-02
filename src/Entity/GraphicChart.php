<?php

namespace App\Entity;

use App\Repository\GraphicChartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GraphicChartRepository::class)]
#[ORM\Table(name: "graphic_chart")]
class GraphicChart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $companyLogo = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $signing = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $titleColor = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Font $titleFont = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Font $contentFont = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: "company_id", referencedColumnName: "id", nullable: false)]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
    }

    public function setCompanyLogo(?string $companyLogo): static
    {
        $this->companyLogo = $companyLogo;
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

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;
        return $this;
    }

    public function getTitleColor(): ?string
    {
        return $this->titleColor;
    }

    public function setTitleColor(?string $titleColor): static
    {
        $this->titleColor = $titleColor;
        return $this;
    }

    public function getTitleFont(): ?Font
    {
        return $this->titleFont;
    }

    public function setTitleFont(?Font $titleFont): static
    {
        $this->titleFont = $titleFont;

        return $this;
    }

    public function getContentFont(): ?Font
    {
        return $this->contentFont;
    }

    public function setContentFont(?Font $contentFont): static
    {
        $this->contentFont = $contentFont;

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
}
