<?php

namespace App\Service;

use App\Entity\Font;
use App\Repository\FontRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\TryCatch;

class FontService
{
    private $fontRepository;
    private $entityManager;

    public function __construct(FontRepository $fontRepository, EntityManagerInterface $entityManager)
    {
        $this->fontRepository = $fontRepository;
        $this->entityManager = $entityManager;
    }

    public function addFontIfNotExists(string $font): bool
    {
        $existingFont = $this->fontRepository->findOneByName($font);

        if($existingFont){
            return false;
        }

        $newfont = new Font();
        $newfont->setName($font);

        try {
            $this->entityManager->persist($newfont);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return true;
    }
}