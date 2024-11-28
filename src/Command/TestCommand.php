<?php

namespace App\Command;

use App\Filter\PagesFilter;
use App\Grabber\Grabber;
use App\Grabber\GrabberInterface;
use App\Resource\GoldiUA;
use Override;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[AsCommand(
    name: 'test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(
        private readonly Grabber $grabber
    ) {
        parent::__construct();
    }


    #[Override] protected function configure(): void
    {
        $this
            ->addOption('page-offset', null, InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('page-limit', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this
            ->getGrabber(GoldiUA::class)
            ->addFilter(new PagesFilter(
                offset: $input->getOption('page-offset'),
                limit: $input->getOption('page-limit'),
            ))
            ->grab();

//        $r = $this->locator->get(GoldiUA::class);
//        $r = $r->getExtractor()->extract(
//            new Crawler(
//                node: file_get_contents('https://goldi.ua/catalog/zinocij-odag'),
//                uri: 'https://goldi.ua/catalog/zinocij-odag'
//            )
//        );

        return Command::SUCCESS;
    }

    /**
     * @template T
     * @param class-string<T> $resourceClass
     * @return GrabberInterface<T>
     */
    private function getGrabber(string $resourceClass): GrabberInterface
    {
        return ($this->grabber)($resourceClass);
    }
}
