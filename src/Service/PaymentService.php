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

class PaymentService
{
    private $paymentRepository;
    private $mailer;
    private $adminEmail;
    private $csvExporter;
    private $invoiceService;
    private $entityManager;

    public function __construct(PaymentRepository $paymentRepository, MailerInterface $mailer, #[Autowire('%admin_email%')] string $adminEmail, CsvExporter $csvExporter, InvoiceService $invoiceService, EntityManagerInterface $entityManager)
    {
        $this->paymentRepository = $paymentRepository;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
        $this->invoiceService = $invoiceService;
        $this->entityManager = $entityManager;
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
                'invoice_number' => $invoice->getInvoiceNumber(),
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

        $amountDetails = $this->invoiceService->getInvoiceDetails($invoice);
        $amountHt = $amountDetails['amount_ht'];
        $quotation = $invoice->getQuotation();
        $type = $quotation->getType();

        $payment->setAmount($amountHt);
        $payment->setInvoice($invoice);

        if ($type === 'OneTime') {
            $this->setOneTimePayment($payment, $status, $now);
        } elseif ($type === 'Recurring') {
            $this->setRecurringPayment($payment, $status, $now);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    private function setOneTimePayment(Payment $payment, string $status, \DateTime $now): void
    {
        $oneTimePayment = new OneTimePayment();
        $payment->setOneTimePayment($oneTimePayment);
        $oneTimePayment->setStatus($status);
        $oneTimePayment->setPaymentDate($now);

        $this->entityManager->persist($oneTimePayment);
    }

    private function setRecurringPayment(Payment $payment, string $status, \DateTime $now): void
    {
        $recurringPayment = new RecurringPayment();
        $payment->setRecurringPayment($recurringPayment);
        $recurringPayment->setStatus($status);
        $recurringPayment->setPaymentDate($now);
        $recurringPayment->setStartDate($now);
        $recurringPayment->setEndDate($now->add(new \DateInterval('P1M')));

        $this->entityManager->persist($recurringPayment);
    }
}