<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DesignGuideController extends AbstractController
{
    #[Route('/design/guide', name: 'site.design_guide')]
    public function index(): Response
    {
        return $this->render('site/design_guide/index.html.twig');
    }
}
