<?php

namespace App\Service;

use App\Entity\OneTimePayment;
use App\Entity\RecurringPayment;
use App\Entity\User;
use App\Repository\PaymentRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

class PaymentService
{
    private $paymentRepository;
    private $mailer;
    private $adminEmail;
    private $csvExporter;
    private $invoiceService;

    public function __construct(PaymentRepository $paymentRepository, MailerInterface $mailer, #[Autowire('%admin_email%')] string $adminEmail, CsvExporter $csvExporter, InvoiceService $invoiceService)
    {
        $this->paymentRepository = $paymentRepository;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
        $this->invoiceService = $invoiceService;
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
                $status = $payment->getOneTimePayment()->getStatus();
                $paymentDate = $payment->getOneTimePayment()->getPaymentDate();
            } elseif ($type === 'Recurring') {
                $status = $payment->getRecurrungPayment()->getStatus();
                $paymentDate = $payment->getRecurrungPayment()->getPaymentDate();
            }
            
            $rows[] = [
                'id' => $payment->getId(),
                'uid' => $payment->getUid(),
                'invoice_number' => $invoice->getInvoiceNumber(),
                'type' => $type,
                'amountHt' => $amountHt,
                'amountTtc' => $amountTtc,
                'status' => $status,
                'paymentDate' => $paymentDate->format('Y-m-d H:i:s'),
            ];
        }

        return $rows;
    }
}