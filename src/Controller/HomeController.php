<?php

namespace App\Controller;

use App\Service\JWTService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'site.home')]
    public function index(): Response
    {
        return $this->render('site/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/quotation/validation/{token}', name: 'site.home.quotation.validation')]
    public function validateQuotation($token, Request $request, JWTService $jWTService): Response
    {
        return $this->render('site/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
