<?php

namespace App\Controller;

use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(): Response
    {
        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
        ]);
    }
    
    #[Route('/search/{name}', name: 'app_search_name')]
    public function searchName(string $name, SectionRepository $sectionRepository): Response
    {
        $links = $sectionRepository->findLinksByName($name);
        
        return $this->json($links); 
    }
}
