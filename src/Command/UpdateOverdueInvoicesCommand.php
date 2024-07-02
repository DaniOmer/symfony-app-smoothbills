<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\InvoiceStatus;


#[AsCommand(
    name: 'app:update-overdue-invoices',
    description: 'Update overdue invoices status to Unpaid.',
)]
class UpdateOverdueInvoicesCommand extends Command
{
    private $invoiceRepository;
    private $entityManager;

    public function __construct(InvoiceRepository $invoiceRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Update overdue invoices status to Unpaid.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTime();
        $overdueInvoices = $this->invoiceRepository->findOverdueInvoices($today);

        $unpaidStatus = $this->entityManager->getRepository(InvoiceStatus::class)->findOneBy(['name' => 'Unpaid']);

        foreach ($overdueInvoices as $invoice) {
            $invoice->setInvoiceStatus($unpaidStatus);
            $this->entityManager->persist($invoice);
        }

        $this->entityManager->flush();

        $output->writeln('Overdue invoices have been updated to Unpaid status.');

        return Command::SUCCESS;
    }
}
