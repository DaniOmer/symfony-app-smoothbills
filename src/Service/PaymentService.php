<?php

namespace App\Service;

use App\Entity\OneTimePayment;
use App\Entity\Payment;
use App\Entity\RecurringPayment;
use App\Entity\User;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Form\FormInterface;
use App\Utils\NumberGenerator;

class PaymentService
{
    private $paymentRepository;
    private $mailer;
    private $adminEmail;
    private $csvExporter;
    private $invoiceService;
    private $entityManager;
    private $numberGenerator;

    public function __construct(
        PaymentRepository $paymentRepository, 
        MailerInterface $mailer, 
        #[Autowire('%admin_email%')] string $adminEmail, 
        CsvExporter $csvExporter, 
        InvoiceService $invoiceService, 
        EntityManagerInterface $entityManager, 
        NumberGenerator $numberGenerator
    ){
        $this->paymentRepository = $paymentRepository;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
        $this->invoiceService = $invoiceService;
        $this->entityManager = $entityManager;
        $this->numberGenerator = $numberGenerator;
    }

    public function getPaginatedPayments(User $user, $page): PaginationInterface
    {
        $paginateQuotations = $this->paymentRepository->paginatePaymentsByCompany($user, $page);

        return $paginateQuotations;
    }

    public function getPaymentsRows(User $user, $page): Array
    {
        $rows = [];

        foreach ($this->getPaginatedPayments($user, $page) as $payment) {
            $invoice = $payment->getInvoice();
            $quotation = $invoice->getQuotation();

            $type = $quotation->getType();

            $invoice = $payment->getInvoice();
            if ($invoice) {
                $amountDetails = $this->invoiceService->getInvoiceDetails($invoice);
                if ($amountDetails) {
                    $amountHt = $amountDetails['amount_ht'];
                    $amountTtc = $amountDetails['amount_ttc'];
                }
            }

            $status = null;
            $paymentDate = null;

            if ($type === 'OneTime') {
                $oneTimePayment = $payment->getOneTimePayment();
                if ($oneTimePayment) {
                    $status = $oneTimePayment->getStatus();
                    $paymentDate = $oneTimePayment->getPaymentDate();
                }
            } elseif ($type === 'Recurring') {
                $recurringPayment = $payment->getRecurringPayment();
                if ($recurringPayment) {
                    $status = $recurringPayment->getStatus();
                    $paymentDate = $recurringPayment->getPaymentDate();
                }
            }
            
            $paymentDateFormatted = $paymentDate ? $paymentDate->format('Y-m-d H:i:s') : null;
            
            $rows[] = [
                'id' => $payment->getId(),
                'uid' => $payment->getUid(),
                'payment_number' => $payment->getPaymentNumber(),
                'type' => $type,
                'amountHt' => $amountHt,
                'amountTtc' => $amountTtc,
                'status' => $status,
                'paymentDate' => $paymentDateFormatted,
            ];
        }

        return $rows;
    }

    public function createPayment($invoice): void
    {
        $now = new \DateTime();
        $payment = new Payment();
        $status = 'Pending';

        $company = $invoice->getCompany();

        $amountDetails = $this->invoiceService->getInvoiceDetails($invoice);
        $amountHt = $amountDetails['amount_ht'];
        $quotation = $invoice->getQuotation();
        $type = $quotation->getType();
        $paymentNumber = $this->generatePaymentNumber($company->getId());

        $payment->setAmount($amountHt);
        $payment->setInvoice($invoice);
        $payment->setPaymentNumber($paymentNumber);

        if ($type === 'OneTime') {
            $oneTimePayment = $this->setOneTimePayment($payment, $status, $now);
            $payment->setOneTimePayment($oneTimePayment);
        } elseif ($type === 'Recurring') {
            $recurringPayment = $this->setRecurringPayment($payment, $status, $now);
            $payment->setRecurringPayment($recurringPayment);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    public function updatePaymentStatus(Payment $payment, $status): void
    {
        $quotation = $payment->getInvoice()->getQuotation();
        $type = $quotation->getType();
        $now = new \DateTime();

        if ($type === 'OneTime') {
            $oneTimePayment = $this->setOneTimePayment($payment, $status, $now);
            $payment->setOneTimePayment($oneTimePayment);
        } elseif ($type === 'Recurring') {
            $recurringPayment = $this->setRecurringPayment($payment, $status, $now);
            $payment->setRecurringPayment($recurringPayment);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    private function generatePaymentNumber(int $companyId): string
    {
        $prefix = 'PA';
        $lastPaymentNumber = $this->paymentRepository->getLastPaymentNumberForCompany($companyId);

        $paymentNumber = $this->numberGenerator->generateDocumentNumber($lastPaymentNumber, $prefix);

        return $paymentNumber;
    }

    private function setOneTimePayment(Payment $payment, string $status, \DateTime $now): OneTimePayment
    {
        $oneTimePayment = $payment->getOneTimePayment();

        if(!$oneTimePayment){
            $oneTimePayment = new OneTimePayment();
        }

        if($status === 'Paid'){
            $oneTimePayment->setPaymentDate($now);
        }
        
        $oneTimePayment->setStatus($status);

        $this->entityManager->persist($oneTimePayment);

        return $oneTimePayment;
    }

    private function setRecurringPayment(Payment $payment, string $status, \DateTime $now): RecurringPayment
    {
        $recurringPayment = $payment->getRecurringPayment();

        if (!$recurringPayment){
            $recurringPayment = new RecurringPayment();
        }

        if($status === 'Paid'){
            $recurringPayment->setPaymentDate($now);
            $recurringPayment->setStartDate($now);
            $recurringPayment->setEndDate($now->add(new \DateInterval('P1M')));
        }
        
        $recurringPayment->setStatus($status);
        
        $this->entityManager->persist($recurringPayment);

        return $recurringPayment;
    }
}