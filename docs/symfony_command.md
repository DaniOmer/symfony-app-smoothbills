# Symfony Console Command Documentation

Symfony's Console component allows you to create command-line commands for various tasks such as running cron jobs, importing/exporting data, or interacting with the database.

## Creating a Command

To create a new command :

-   run the following command `php bin/console make:command CommandName`
-   edit the corresponding class inside at `src/Command/CommandName.php`
-   add arguments and options to the command by defining them in the `configure()`method
-   put the command logic inside the `execute()`method

Here's an example of basic command Class :

```php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'my-command',
    description: 'My command description',
)]
class MyCommand extends Command
{

    public function __construct()
    {
        parent::__construct();;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::REQUIRED, 'My command arg1')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        // Logic goes here
    }
}
```
