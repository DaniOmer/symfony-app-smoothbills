<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/home')]
class GeneralController extends AbstractController
{
    #[Route('/', name: 'dashboard.home')]
    public function index(): Response
    {
        return $this->render('dashboard/home/index.html.twig', [
            'controller_name' => 'GeneralController',
        ]);
    }
}
