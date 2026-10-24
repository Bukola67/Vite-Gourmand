<?php

namespace App\Command;

use App\Repository\CustomerOrderRepository;
use App\Service\MongoStatsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stats:rebuild',
    description: 'Reconstruit les statistiques MongoDB à partir des commandes MySQL.'
)]
final class RebuildOrderStatsCommand extends Command
{
    public function __construct(
        private readonly CustomerOrderRepository $customerOrderRepository,
        private readonly MongoStatsService $mongoStatsService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning('Cette commande efface les statistiques MongoDB puis les reconstruit depuis MySQL.');

        if (!$io->confirm('Confirmer la reconstruction des statistiques ?', false)) {
            $io->note('Reconstruction annulée.');
            return Command::SUCCESS;
        }

        $orders = $this->customerOrderRepository->findAll();

        $this->mongoStatsService->rebuildStats($orders);

        $io->success(sprintf(
            'Statistiques reconstruites à partir de %d commande(s).',
            count($orders)
        ));

        return Command::SUCCESS;
    }
}