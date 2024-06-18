<?php

namespace App\Controller;

use App\Entity\GraphicChart;
use App\Form\GraphicChartType;
use App\Repository\GraphicChartRepository;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/settings/graphic/chart')]
class GraphicChartController extends AbstractController
{
    use ProfileCompletionTrait;
    private $entityManager;
    private $graphicChartRepository;
    private $userRegistrationChecker;

    public function __construct(EntityManagerInterface $entityManager, UserRegistrationChecker $userRegistrationChecker, GraphicChartRepository $graphicChartRepository)
    {
        $this->entityManager = $entityManager;
        $this->graphicChartRepository = $graphicChartRepository;
        $this->userRegistrationChecker = $userRegistrationChecker;
    }

    #[Route('/', name: 'dashboard.settings.graphic', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $company = $this->getUser()->getCompany();
        $graphicChart = $this->graphicChartRepository->findOneBy(['company' => $company]);

        if(!$graphicChart) {
            $graphicChart = new GraphicChart();
            $graphicChart->setCompany($company);
        }

        $form = $this->createForm(GraphicChartType::class, $graphicChart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($graphicChart);
            $entityManager->flush();

            $this->addFlash('success', 'Votre charte graphique a bien été enregistré');

            return $this->redirectToRoute('dashboard.settings.graphic', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/dashboard/graphic_chart/manage.html.twig', [
            'graphic_chart' => $graphicChart,
            'form' => $form,
        ]);
    }
}
