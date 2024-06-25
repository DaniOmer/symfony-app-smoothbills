<?php

namespace App\Controller;

use App\Entity\Quotation;
use App\Service\JWTService;
use App\Service\QuotationService;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/quotation/validation/{token}', name: 'site.home.quotation.validation', methods: ['GET', 'POST'])]
    public function validateQuotation($token, Request $request, JWTService $jWTService, EntityManagerInterface $entityManager, QuotationService $quotationService): Response
    {
        $decodedToken = base64_decode($token);
        $jwtData = $jWTService->parseToken($decodedToken);

        if (!$jwtData || !isset($jwtData['quotation_uid']) || !isset($jwtData['exp'])) {
            throw $this->createNotFoundException('Invalid or missing JWT data');
        }

        $uid = $jwtData['quotation_uid'];
        $expiry = $jwtData['exp'];
        $now = new \DateTime();

        $quotation = $entityManager->getRepository(Quotation::class)->findOneBy(['uid' => $uid]);
        if (!$quotation || $expiry < $now->getTimestamp()) {
            throw $this->createNotFoundException('Devis non trouvé ou expiré !');
        }
        
        // dd($quotation->getQuotationStatus()->getName() !== 'En attente');
        if($quotation->getQuotationStatus()->getName() !== 'En attente'){
            throw $this->createNotFoundException('Le devis a déjà été validé !');
        }

        $quotationDetails = $quotationService->getQuotationDetails($quotation);

        if($request->isMethod('POST')){
            $action = $request->request->get('action');
            $tokenId = $action === 'accept' ? 'accepted_action' : 'rejected_action';
            $submittedToken = $request->getPayload()->get('_csrf_token');

            
            if (!$this->isCsrfTokenValid($tokenId, $submittedToken)) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            if($action === 'accept'){
                $quotationService->validateQuotation($quotation, "Accepté");
            }elseif($action=== 'reject'){
                $quotationService->validateQuotation($quotation, "Refusé");
            }
        }


        return $this->render('site/home/validation/quotation.html.twig', [
            'token' => $token,
            'quotation' => $quotation,
            'quotationDetails' => $quotationDetails['quotationDetails'],
            'totalPriceWithoutTax' => $quotationDetails['totalPriceWithoutTax'],
            'totalPriceWithTax' => $quotationDetails['totalPriceWithTax'],
        ]);
    }
}
