<?php

namespace App\Command;

use App\Message\ProductSynchronizationMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'sync:products',
    description: 'Synchronize a products',
)]
class SyncProductsCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED)
            ->addOption('offset', 'o', InputOption::VALUE_OPTIONAL, '', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->bus->dispatch(new ProductSynchronizationMessage(
            source: $input->getArgument('source'),
            offset: $input->getOption('offset'),
            limit: $input->getOption('limit')
        ));

        return Command::SUCCESS;
    }
}
