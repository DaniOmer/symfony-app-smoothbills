<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Font;
use App\Service\FontService;

#[AsCommand(
    name: 'app:AddFont',
    description: 'Adds a font to the font entity',
)]
class AddFontCommand extends Command
{
    private $fontService;

    public function __construct(FontService $fontService)
    {
        parent::__construct();
        $this->fontService = $fontService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The JSON file containing fonts.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if (!file_exists($file)) {
            $io->error(sprintf('The file "%s" does not exist.', $file));
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($file);
        $fonts = json_decode($jsonContent, true);

        if ($fonts === null) {
            $io->error('Unable to parse JSON file.');
            return Command::FAILURE;
        }

        foreach ($fonts as $font) {
            $fontName = $font['name'];

            try {
                if($this->fontService->addFontIfNotExists($fontName)){
                    $io->success(sprintf('Font "%s" has been added successfully.', $fontName));
                }else{
                    $io->warning(sprintf('Font "%s" already exists.', $fontName));
                }
            } catch (\Exception $e) {
                $io->error(sprintf('An error occured while adding the font "%s".', $fontName));
                return Command::FAILURE;
            }
        }


        $io->success('Font JSON file processed successfully.');
        return Command::SUCCESS;
    }
}
