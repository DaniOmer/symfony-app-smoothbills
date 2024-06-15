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

        if (in_array($routeName, ['site.complete_registration']) || str_starts_with($routeName, 'site.')) {
            return;
        }

        $isRegistrationComplete = $this->registrationChecker->isRegistrationComplete();
        $this->twig->addGlobal('isRegistrationComplete', $isRegistrationComplete);

        // if (!$isRegistrationComplete) {
        //     $url = $this->router->generate('site.complete_registration');
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
