<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Company;
use App\Form\CompanyType;
use App\Service\CompanyService;
use App\Service\UserRegistrationChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/settings/company')]
class CompanyController extends AbstractController
{
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    #[Route('/', name: 'dashboard.settings.company', methods: ['GET', 'POST'])]
    public function manage(Request $request, UserRegistrationChecker $userRegistrationChecker): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Accès refusé : Cette page est réservée aux administrateurs.');

        $user = $this->getUser();
        $company = $this->getUser()->getCompany();

        if (!$company) {
            $company = new Company();
            $address = new Address();
        } else {
            $address = $company->getAddress();
        }

        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $this->companyService->manageCompany($form, $address, $company, $user);
                $userRegistrationChecker->updateRegistrationCache($user->getId());
                $this->addFlash('success_company', 'Les informations de votre entreprise ont bien été enregistré');
            } catch (\Exception $e) {
                $this->addFlash('error_company', 'Une erreur est survenue lors de l\'enregistrement de votre entreprise');
                return $this->redirectToRoute('dashboard.settings.company', [], Response::HTTP_SEE_OTHER);
            }

            $userRegistrationChecker->updateRegistrationCache($user->getId());

            $this->addFlash('success', 'Les informations de votre entreprise ont bien été enregistré');
            return $this->redirectToRoute('dashboard.settings.company', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/company/manage.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }
}