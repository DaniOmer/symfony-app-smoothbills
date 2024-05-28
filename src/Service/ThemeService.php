<?php

namespace App\Service;

use App\Entity\Theme;
use App\Repository\FontRepository;
use App\Repository\ThemeRepository;
use App\Utils\ColorConverter;
use Doctrine\ORM\EntityManagerInterface;

class ThemeService
{
    private $fontRepository;
    private $entityManager;
    private $colorConverter;

    public function __construct(ThemeRepository $themeRepository, FontRepository $fontRepository, EntityManagerInterface $entityManager, ColorConverter $colorConverter)
    {
        $this->fontRepository = $fontRepository;
        $this->entityManager = $entityManager;
        $this->colorConverter = $colorConverter;
    }

    public function createTheme(array $themeData): bool
    {
        $theme = new Theme();

        if (!$this->validateThemeData($themeData)) {
            throw new \InvalidArgumentException('Invalid theme data');
        }

        $this->entityManager->beginTransaction();

        try {
            $theme->setName($themeData['name']);
            $theme->setPrimaryColor($this->colorConverter->hexToRgba($themeData['primary_color']));
            $theme->setSecondaryColor($this->colorConverter->hexToRgba($themeData['secondary_color']));
            $theme->setTertiaryColor($this->colorConverter->hexToRgba($themeData['tertiary_color']));
            $theme->setBgColor($this->colorConverter->hexToRgba($themeData['bg_color']));
            $theme->setTitleFont($this->fontRepository->findOneByName($themeData['title_font']));
            $theme->setSubtitleFont($this->fontRepository->findOneByName($themeData['subtitle_font']));
            $theme->setContentFont($this->fontRepository->findOneByName($themeData['content_font']));
            $theme->setIsActive($themeData['is_active']);
            $theme->setSidebarPosition($themeData['sidebar_position']);

            $this->entityManager->persist($theme);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return true;
    }

    private function validateThemeData(array $themeData): bool
    {
        return isset($themeData['name'], $themeData['primary_color'], $themeData['secondary_color'], $themeData['tertiary_color'], $themeData['bg_color'], $themeData['title_font'], $themeData['subtitle_font'], $themeData['content_font'], $themeData['is_active'], $themeData['sidebar_position']);
    }

}