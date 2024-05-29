<?php

namespace App\EventSubscriber;

use App\Repository\ThemeRepository;
use App\Repository\UserThemeRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $security;
    private $themeRepository;
    private $userThemeRepository;

    public function __construct(Environment $twig, ThemeRepository $themeRepository, UserThemeRepository $userThemeRepository, Security $security)
    {
        $this->twig = $twig;
        $this->security = $security;
        $this->themeRepository = $themeRepository;
        $this->userThemeRepository = $userThemeRepository;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        /******
            À faire : Mise en cache Redis pour éviter les appels
            à la base de données à chaque chargement de page.
            Récupérer l'ID du user après login pour l'ajouter
            à la clé de stockage dans redis. 
        ******/

        $theme = $this->userThemeRepository->findOneBy(['is_active' => true, 'owner' => $this->security->getUser()]);

        if(!$theme){
            $theme = $this->themeRepository->findOneBy(['name' => 'Default Theme']);
        }

        $this->twig->addGlobal('theme', $theme);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
