<?php

namespace App\EventSubscriber;

use App\Service\UserRegistrationChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class UserRegistrationSubscriber implements EventSubscriberInterface
{
    private $registrationChecker;
    private $router;
    private $twig;

    public function __construct(UserRegistrationChecker $registrationChecker, RouterInterface $router, Environment $twig)
    {
        $this->registrationChecker = $registrationChecker;
        $this->router = $router;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        $isRegistrationComplete = $this->registrationChecker->isRegistrationComplete();
        $this->twig->addGlobal('isRegistrationComplete', $isRegistrationComplete);

        // if (in_array($routeName, ['dashboard.home']) || str_starts_with($routeName, 'site.') || str_starts_with($routeName, 'dashboard.settings.')) {
        //     return;
        // }

        // Cette redirection est un peu tricky et provoque des bugs.
        // La debug bar est désactivé
        // Faudra essayé de comprendre pourquoi mais c'est lié aux redirections hors controller
        // if (!$isRegistrationComplete) {
        //     $url = $this->router->generate('dashboard.settings.company.create');
        //     $event->setController(fn () => new RedirectResponse($url));
        // }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
