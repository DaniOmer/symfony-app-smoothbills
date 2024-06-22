<?php

namespace App\Service;

use App\Entity\Quotation;
use App\Entity\User;
use App\Repository\QuotationRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class QuotationService
{
    private $quotationRepository;
    private $mailer;
    private $adminEmail;
    private $csvExporter;

    public function __construct(QuotationRepository $quotationRepository, MailerInterface $mailer, #[Autowire('%admin_email%')] string $adminEmail, CsvExporter $csvExporter)
    {
        $this->quotationRepository = $quotationRepository;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->csvExporter = $csvExporter;
    }

    public function getPaginatedQuotations(User $user, $page): PaginationInterface
    {
        $paginateQuotations = $this->quotationRepository->paginateQuotationsByCompany($user, $page);

        return $paginateQuotations;
    }

    public function getQuotationsRows(User $user, $page): Array
    {
        $rows = [];

        foreach ($this->getPaginatedQuotations($user, $page) as $quotation) {
            $rows[] = [
                'id' => $quotation->getId(),
                'uid' => $quotation->getUid(),
                'name' => $quotation->getUid(),
                'status' => $quotation->getQuotationStatus()->getName(),
                'client' => $quotation->getCustomer()->getName(),
                'sendingDate' => $quotation->getSendingDate() ? $quotation->getSendingDate()->format('Y-m-d H:i:s') : '',
            ];
        }

        return $rows;
    }

    public function sendQuotationMail(Quotation $quotation): void
    {
        $quotationCsvData = $this->csvExporter->exportQuotation($quotation);

        $email = (new TemplatedEmail())
            ->from(new Address($this->adminEmail, 'Smoothbill'))
            ->to($quotation->getCustomer()->getMail())
            ->subject('Nouveau devis créé')
            ->htmlTemplate('site/quotation/mail/export_csv_email.html.twig')
            ->context([
                'quotation' => $quotation,
                'customerName' => $quotation->getCustomer()->getName()
            ])
            ->attach($quotationCsvData, 'quotation.csv', 'text/csv');

        $this->mailer->send($email);
    }

    public function exportAllQuotations(): Response
    {
        $quotations = $this->quotationRepository->findAll();
        $headers = ['ID', 'Nom', 'Status', 'Client', 'Envoyé le'];
        $dataExtractor = function(Quotation $quotation) {
            return [
                $quotation->getId(),
                $quotation->getUid(),
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getSendingDate() ? $quotation->getSendingDate()->format('Y-m-d H:i:s') : '',
            ];
        };

        return $this->csvExporter->exportEntities($quotations, $headers, $dataExtractor, 'all_quotations');
    }
}