<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/dashboard/settings', name: 'dashboard.settings')]
    public function index(): Response
    {
        return $this->render('dashboard/settings/index.html.twig');
    }
}