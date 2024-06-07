<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalNoticeController extends AbstractController
{
    #[Route('/legal/notice', name: 'site.legal_notice')]
    public function index(): Response
    {
        return $this->render('site/legal_notice/index.html.twig', [
            'controller_name' => 'LegalNoticeController',
        ]);
    }
}
