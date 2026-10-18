<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use MongoDB\Client;
use MongoDB\Driver\Exception\BulkWriteException;

class MongoStatsService
{
    private \MongoDB\Collection $menuStats;
    private \MongoDB\Collection $monthlyStats;
    private \MongoDB\Collection $processedOrders;
    private \MongoDB\Collection $completedOrders;

    public function __construct(Client $client, string $mongoDbName)
    {
        $database = $client->selectDatabase($mongoDbName);

        $this->menuStats = $database->selectCollection('menu_stats');
        $this->monthlyStats = $database->selectCollection('menu_monthly_stats');
        $this->processedOrders = $database->selectCollection('processed_order_stats');
        $this->completedOrders = $database->selectCollection('completed_order_stats');

        $this->createIndexes();
    }

    private function createIndexes(): void
    {
        $this->processedOrders->createIndex(['orderId' => 1], ['unique' => true]);
        $this->completedOrders->createIndex(['orderId' => 1], ['unique' => true]);
        $this->monthlyStats->createIndex(['menuId' => 1, 'month' => 1], ['unique' => true]);
    }

    /**
     * Comptabilise une commande enregistrée.
     * Cette méthode est idempotente : une commande déjà traitée n'est jamais recomptée.
     */
    public function incrementOrderStats(CustomerOrder $order): void
    {
        $orderId = (int) $order->getId();

        try {
            $this->processedOrders->insertOne([
                'orderId' => $orderId,
                'processedAt' => new \DateTimeImmutable(),
            ]);
        } catch (BulkWriteException $exception) {
            // Index unique violé : la commande a déjà été comptabilisée.
            return;
        }

        $menu = $order->getMenu();
        $month = $order->getCreatedAt()->format('Y-m');
        $totalPrice = (float) $order->getTotalPrice();

        $this->menuStats->updateOne(
            ['menuId' => $menu->getId()],
            [
                '$setOnInsert' => [
                    'menuId' => $menu->getId(),
                    'menuTitle' => $menu->getTitle(),
                ],
                '$inc' => [
                    'totalOrders' => 1,
                ],
            ],
            ['upsert' => true]
        );

        $this->monthlyStats->updateOne(
            [
                'menuId' => $menu->getId(),
                'month' => $month,
            ],
            [
                '$setOnInsert' => [
                    'menuId' => $menu->getId(),
                    'month' => $month,
                ],
                '$inc' => [
                    'orders' => 1,
                ],
            ],
            ['upsert' => true]
        );
    }

    /**
     * Comptabilise le chiffre d'affaires encaissé lorsqu'une prestation est terminée.
     * Cette méthode est idempotente : une commande déjà terminée n'est jamais recomptée.
     */
    public function markOrderCompleted(CustomerOrder $order): void
    {
        $orderId = (int) $order->getId();

        try {
            $this->completedOrders->insertOne([
                'orderId' => $orderId,
                'completedAt' => new \DateTimeImmutable(),
            ]);
        } catch (BulkWriteException $exception) {
            // Index unique violé : le chiffre d'affaires a déjà été comptabilisé.
            return;
        }

        $menu = $order->getMenu();
        $month = $order->getCreatedAt()->format('Y-m');
        $totalPrice = (float) $order->getTotalPrice();

        $this->menuStats->updateOne(
            ['menuId' => $menu->getId()],
            [
                '$setOnInsert' => [
                    'menuId' => $menu->getId(),
                    'menuTitle' => $menu->getTitle(),
                ],
                '$inc' => [
                    'completedOrders' => 1,
                    'completedRevenue' => $totalPrice,
                ],
            ],
            ['upsert' => true]
        );

        $this->monthlyStats->updateOne(
            [
                'menuId' => $menu->getId(),
                'month' => $month,
            ],
            [
                '$setOnInsert' => [
                    'menuId' => $menu->getId(),
                    'month' => $month,
                ],
                '$inc' => [
                    'completedOrders' => 1,
                    'completedRevenue' => $totalPrice,
                ],
            ],
            ['upsert' => true]
        );
    }

    public function getMenuStats(): array
    {
        return $this->menuStats
            ->find([], ['sort' => ['totalOrders' => -1]])
            ->toArray();
    }

    public function getRevenueByMenuAndPeriod(
        ?int $menuId,
        ?string $startMonth,
        ?string $endMonth
    ): array {
        $filter = [];

        if ($menuId !== null) {
            $filter['menuId'] = $menuId;
        }

        if ($startMonth !== null || $endMonth !== null) {
            $filter['month'] = [];

            if ($startMonth !== null) {
                $filter['month']['$gte'] = $startMonth;
            }

            if ($endMonth !== null) {
                $filter['month']['$lte'] = $endMonth;
            }
        }

        $monthlyStats = $this->monthlyStats->find($filter)->toArray();

        $results = [];

        foreach ($monthlyStats as $stat) {
            $menuId = (int) $stat['menuId'];

            if (!isset($results[$menuId])) {
                $results[$menuId] = [
                    'menuId' => $menuId,
                    'menuTitle' => $this->getMenuTitle($menuId),
                    'revenue' => 0.0,
                    'totalOrders' => 0,
                ];
            }

            $results[$menuId]['revenue'] += (float) ($stat['completedRevenue'] ?? 0);
            $results[$menuId]['totalOrders'] += (int) ($stat['completedOrders'] ?? 0);
        }

        return array_values($results);
    }

    private function getMenuTitle(int $menuId): string
    {
        $menuStat = $this->menuStats->findOne(['menuId' => $menuId]);

        return (string) ($menuStat['menuTitle'] ?? 'Menu #' . $menuId);
    }

    /**
     * Reconstruit toutes les statistiques MongoDB à partir des commandes MySQL.
     * À utiliser uniquement manuellement, en cas d'écart ou après une panne MongoDB.
     */
    public function rebuildStats(iterable $orders): void
    {
        $this->menuStats->deleteMany([]);
        $this->monthlyStats->deleteMany([]);
        $this->processedOrders->deleteMany([]);
        $this->completedOrders->deleteMany([]);

        foreach ($orders as $order) {
            if (!$order instanceof CustomerOrder) {
                continue;
            }

            $this->incrementOrderStats($order);

            if ($order->getStatus() === 'terminee') {
                $this->markOrderCompleted($order);
            }
        }
    }
}