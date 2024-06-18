<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\Types\UuidType;

trait UuidTypeTrait
{
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private ?Uuid $uid = null;

    public function __construct()
    {
        $this->uid = Uuid::v7();
    }

    public function getUid(): ?Uuid
    {
        return $this->uid;
    }

    public function setUid(?Uuid $uid): self
    {
        $this->uid = $uid;
        return $this;
    }
}