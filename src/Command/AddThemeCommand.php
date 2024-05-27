<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'AddTheme',
    description: 'Add new theme to smoothbill application',
)]
class AddThemeCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The JSON file containing themes details.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if(!file_exists($file)){
            $io->error(sprintf('The file "%s" does not exist.', $file));
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($file);
        $theme = json_decode($jsonContent);

        if($theme === null){
            $io->error("Unable to parse the provided JSON file.");
            return Command::FAILURE;
        }

        // Process the insertions and updates

        $io->success('Theme JSON file processed successfully.');

        return Command::SUCCESS;
    }
}
