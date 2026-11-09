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
    description: 'Reconstruit les statistiques des commandes créées à partir de MySQL.'
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

        $io->warning('Maintenance requise : suspendre les créations de commandes. La collection created_order_stats est vidée puis reconstruite depuis MySQL (tous statuts, annulations incluses pour le moment).');

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