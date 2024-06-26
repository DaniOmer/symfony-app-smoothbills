<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Quotation;
use App\Service\InvoiceService;
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

    #[Route('/quotation/validation/{token}', name: 'site.home.validation.quotation', methods: ['GET', 'POST'])]
    public function validateQuotation($token, Request $request, JWTService $jWTService, EntityManagerInterface $entityManager, QuotationService $quotationService, InvoiceService $invoiceService): Response
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

        $sendingDate = $quotation->getSendingDate();
        $validityDate = $quotationService->getQuotationValidityDate($sendingDate);
        $quotationDetails = $quotationService->getQuotationDetails($quotation);
        $quotationStatus = $quotation->getQuotationStatus()->getName();
        $config = [
            'token' => $token,
            'quotation' => $quotation,
            'validityDate' => $validityDate,
            'quotationDetails' => $quotationDetails['quotationDetails'],
            'quotationStatus' => $quotationStatus,
            'totalPriceWithoutTax' => $quotationDetails['totalPriceWithoutTax'],
            'totalPriceWithTax' => $quotationDetails['totalPriceWithTax'],
            'graphicChart' => $quotationDetails['graphicChart'],
        ];

        if($quotationStatus !== 'En attente'){
            return $this->render('site/home/validation/quotation.html.twig', $config);
        }

        if($request->isMethod('POST')){
            $action = $request->request->get('action');
            $tokenId = $action === 'accept' ? 'accepted_action' : 'rejected_action';
            $submittedToken = $request->getPayload()->get('_csrf_token');

            if (!$this->isCsrfTokenValid($tokenId, $submittedToken)) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            if($action === 'accept'){
                $quotationService->validateQuotation($quotation, "Accepté");
                $invoice = $invoiceService->createInvoice($quotation);
                
                if ($invoice) {
                    $this->addFlash('success', 'Votre devis a bien été validé.');
                    $invoiceService->sendInvoiceByEmail($invoice);
                } else {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de la facture. Veuillez réessayer.');
                }
            }elseif($action=== 'reject'){
                $quotationService->validateQuotation($quotation, "Refusé");
                $this->addFlash('success', 'Votre devis a bien été rejeté.');
            }

            return $this->redirectToRoute('site.home.validation.quotation', ['token' => $token]);
        }

        return $this->render('site/home/validation/quotation.html.twig', $config);
    }
}
