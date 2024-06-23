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

    public function __construct(PaymentRepository $paymentRepository, MailerInterface $mailer, #[Autowire('%admin_email%')] string $adminEmail, CsvExporter $csvExporter)
    {
        $this->paymentRepository = $paymentRepository;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
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
            $type ='';
            $priceWithoutTax = 0.0;
            $priceWithTax = 0.0;
            $status = '';
            $paymentDate = '';

            if ($payment instanceof OneTimePayment) {
                $type = 'Unique';
                $status = $payment->getStatus();
                $paymentDate = $payment->getPaymentDate()->format('Y-m-d H:i:s');
            } elseif ($payment instanceof RecurringPayment) {
                $type = 'Récurrent';
                $status = $payment->getStatus();
                $paymentDate = $payment->getPaymentDate()->format('Y-m-d H:i:s');
            }

            $invoice = $payment->getInvoice();
            if ($invoice) {
                $amountDetails = $this->getInvoiceDetails($invoice);
                $priceWithoutTax = $amountDetails['amount_ht'];
                $priceWithTax = $amountDetails['amount_ttc'];
            }
            
            $rows[] = [
                'id' => $payment->getId(),
                'uid' => $payment->getUid(),
                'type' => $type,
                'priceWithoutTax' => $priceWithoutTax,
                'priceWithTax' => $priceWithTax,
                'status' => $status,
                'paymentDate' => $paymentDate,
            ];
        }

        return $rows;
    }
}