<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;

class MongoStatsService
{
    private Collection $createdOrders;
    private bool $indexesInitialized = false;

    public function __construct(Client $client, string $mongoDbName)
    {
        // Nouvelle collection : les anciens compteurs de commandes terminées
        // sont conservés mais ne sont plus utilisés par ce service.
        $this->createdOrders = $client->selectDatabase($mongoDbName)
            ->selectCollection('created_order_stats');
    }

    private function ensureIndexes(): void
    {
        if ($this->indexesInitialized) {
            return;
        }
        // L'index unique sur _id est déjà fourni par MongoDB.
        $this->createdOrders->createIndex(['month' => 1, 'menuId' => 1]);
        $this->indexesInitialized = true;
    }

    /**
     * Une commande MySQL = un document MongoDB identifié par son ID.
     * Répéter l'appel remplace les valeurs ; aucun compteur n'est incrémenté.
     * Tous les statuts sont actuellement inclus (annulations : évolution différée).
     */
    public function recordOrderCreated(CustomerOrder $order): void
    {
        $menu = $order->getMenu();
        $createdAt = $order->getCreatedAt();
        $totalPrice = $order->getTotalPrice();

        if ($order->getId() === null || $menu?->getId() === null || $createdAt === null
            || $totalPrice === null) {
            throw new \InvalidArgumentException('La commande doit être enregistrée et complète avant sa projection.');
        }
        if (!preg_match('/^([0-9]{1,8})(?:\.([0-9]{1,2}))?$/D', $totalPrice, $parts)) {
            throw new \InvalidArgumentException('Le montant de la commande est invalide.');
        }
        // Additionner des entiers évite les imprécisions de calcul des flottants.
        $totalPriceCents = (int) $parts[1] * 100
            + (int) str_pad($parts[2] ?? '', 2, '0');
        $month = $createdAt->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m');

        $this->ensureIndexes();
        $this->createdOrders->updateOne(
            ['_id' => (int) $order->getId()],
            ['$set' => [
                'menuId' => (int) $menu->getId(),
                'menuTitle' => (string) $menu->getTitle(),
                'createdAt' => new UTCDateTime($createdAt),
                'month' => $month,
                'totalPriceCents' => $totalPriceCents,
            ]],
            ['upsert' => true]
        );
    }

    /**
     * Même ensemble de données pour le graphique ET le tableau.
     * Les bornes mensuelles de création sont inclusives.
     * @return list<array{menuId: int, menuTitle: string, revenue: string, totalOrders: int}>
     */
    public function getRevenueByMenuAndPeriod(
        ?int $menuId,
        ?string $startMonth,
        ?string $endMonth
    ): array {
        $filter = [];
        if ($menuId !== null) {
            $filter['menuId'] = $menuId;
        }
        if ($startMonth !== null) {
            $filter['month']['$gte'] = $startMonth;
        }
        if ($endMonth !== null) {
            $filter['month']['$lte'] = $endMonth;
        }

        $rows = $this->createdOrders->aggregate([
            ['$match' => (object) $filter],
            ['$sort' => ['createdAt' => 1, '_id' => 1]],
            ['$group' => [
                '_id' => '$menuId',
                'menuTitle' => ['$last' => '$menuTitle'],
                'revenueCents' => ['$sum' => '$totalPriceCents'],
                'totalOrders' => ['$sum' => 1],
            ]],
            ['$sort' => ['totalOrders' => -1, '_id' => 1]],
        ]);

        $results = [];
        foreach ($rows as $row) {
            $cents = (int) $row['revenueCents'];
            $results[] = [
                'menuId' => (int) $row['_id'],
                'menuTitle' => (string) $row['menuTitle'],
                'revenue' => sprintf('%d.%02d', intdiv($cents, 100), $cents % 100),
                'totalOrders' => (int) $row['totalOrders'],
            ];
        }
        return $results;
    }

    /**
     * Reconstruction manuelle depuis MySQL : tous les statuts sont inclus.
     * À exécuter en maintenance : la reconstruction globale n'est pas atomique.
     */
    public function rebuildStats(iterable $orders): void
    {
        $this->ensureIndexes();
        $this->createdOrders->deleteMany([]);
        foreach ($orders as $order) {
            if ($order instanceof CustomerOrder) {
                $this->recordOrderCreated($order);
            }
        }
    }
}
