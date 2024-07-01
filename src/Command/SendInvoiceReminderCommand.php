<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceService;


#[AsCommand(
    name: 'SendInvoiceReminderCommand',
    description: 'Send reminders for overdue invoices.',
)]
class SendInvoiceReminderCommand extends Command
{
    protected static $defaultName = 'app:send-invoice-reminders';
    private $invoiceRepository;
    private $invoiceService;

    public function __construct(InvoiceRepository $invoiceRepository, InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
    }

    protected function configure()
    {
        $this->setDescription('Send reminders for overdue invoices.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTime();
        $overdueInvoices = $this->invoiceRepository->findOverdueInvoices($today);

        foreach ($overdueInvoices as $invoice) {
            $this->invoiceService->sendInvoiceReminder($invoice);
        }

        $output->writeln('Reminders sent successfully!');

        return Command::SUCCESS;
    }
}
