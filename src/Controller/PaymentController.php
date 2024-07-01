<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Service\PaymentService;
use App\Service\UserRegistrationChecker;
use App\Trait\ProfileCompletionTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/payment')]
class PaymentController extends AbstractController
{
    use ProfileCompletionTrait;

    private $userRegistrationChecker;
    private $paymentService;

    public function __construct(UserRegistrationChecker $userRegistrationChecker, PaymentService $paymentService)
    {
        $this->userRegistrationChecker = $userRegistrationChecker;
        $this->paymentService = $paymentService;
    }

    #[Route('/', name: 'dashboard.payment.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $user = $this->getUser();
        $page = $request->query->getInt('page', 1);
        $paginatePayments = $this->paymentService->getPaginatedPayments($user, $page);
        $headers = ['Numéro Facture', 'Type', 'Montant HT', 'Montant TTC', 'Status', 'Date de paiement'];
        $rows = $this->paymentService->getPaymentsRows($user, $page);

        $config = [
            'headers' => $headers,
            'rows' => $rows,
            'payments' => $paginatePayments,
            'actions' => [
                ['route' => 'dashboard.payment.edit', 'label' => 'Modifier'],
            ]
        ];

        return $this->render('dashboard/payment/index.html.twig', $config);
    }

    #[Route('/{uid}/edit', name: 'dashboard.payment.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $uid, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($redirectResponse = $this->isProfileComplete($this->userRegistrationChecker)) {
            return $redirectResponse;
        }

        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $payment = $entityManager->getRepository(Payment::class)->findOneBy(['uid' => $uid]);
            $status = $form->get('status')->getData();

            try {
                $this->paymentService->updatePaymentStatus($payment, $status);
                $this->paymentService->updateInvoiceStatus($payment, $status);
                $this->addFlash('success_payment', 'Le paiement a été mis à jour avec succès.');
                return $this->redirectToRoute('dashboard.payment.index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error_payment', 'Une erreur est survenue lors de la mise à jour du paiement.');
                return $this->redirectToRoute('dashboard.payment.index');
            }

            return $this->redirectToRoute('dashboard.payment.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }
}