<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Font;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'AddFont',
    description: 'Adds a font to the font entity',
)]
class AddFontCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
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

        $this->insertFont($fonts);

        $io->success('Font JSON file procssed successfully.');

        return Command::SUCCESS;
    }

    public function insertFont(array $fonts): void
    {
        $entityManager = $this->entityManager;
        $entityManager->beginTransaction();
        
        try {
            foreach($fonts as $font) {
                $fontName = $font['name'];
    
                $existingFont = $entityManager->getRepository(Font::class)->findOneBy(['name' => $fontName]);
                if(!$existingFont) {
                    $newFont = new Font();
                    $newFont->setName($fontName);
    
                    $entityManager->persist($newFont);
                }
            }
            
            $entityManager->flush();
            $entityManager->commit();
        } catch (\Exception $e) {
            $entityManager->rollback();
            throw $e;
        }
    }
}
