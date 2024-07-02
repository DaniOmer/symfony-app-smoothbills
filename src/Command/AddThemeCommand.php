<?php

namespace App\Command;

use App\Service\ThemeService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:AddTheme',
    description: 'Add new theme to smoothbill application',
)]
class AddThemeCommand extends Command
{
    private $themeService;

    public function __construct(ThemeService $themeService)
    {
        parent::__construct();
        $this->themeService = $themeService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The JSON file containing themes details.')
            ->setHelp('This command allows you to create theme from a JSON file.')
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
        $themes = json_decode($jsonContent, true);

        if($themes === null){
            $io->error("Unable to parse the provided JSON file.");
            return Command::FAILURE;
        }

        foreach($themes as $theme){

            $themeName = $theme['name'];

            try {
                if($this->themeService->createTheme($theme)){
                    $io->success(sprintf('Theme with name "%s" has been added successfully.', $themeName));
                }
            } catch (\Exception $e) {
                $io->error(sprintf('An error occured while adding the theme "%s".', $themeName));
                $io->error(sprintf('Error: "%s".', $e));
                return Command::FAILURE;
            }
        }

        $io->success('Theme JSON file processed successfully.');
        return Command::SUCCESS;
    }
}
