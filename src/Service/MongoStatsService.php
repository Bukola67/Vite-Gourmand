<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use MongoDB\Client;

class MongoStatsService
{
    private \MongoDB\Collection $collection;

    public function __construct(Client $client,string $mongoDbName)
    {
        $this->collection = $client->selectCollection('vite_gourmand', 'menu_stats');
    }

    public function incrementOrderStats(CustomerOrder $order): void
    {
        $menu = $order->getMenu();
        $month = $order->getCreatedAt()->format('Y-m');

        $this->collection->updateOne(
            ['menuId' => $menu->getId()],
            [
                '$setOnInsert' => [
                    'menuId' => $menu->getId(),
                    'menuTitle' => $menu->getTitle(),
                ],
                '$inc' => [
                    'totalOrders' => 1,
                    'totalRevenue' => (float) $order->getTotalPrice(),
                ],
            ],
            ['upsert' => true]
        );

        $this->collection->updateOne(
            [
                'menuId' => $menu->getId(),
                'monthlyBreakdown.month' => $month,
            ],
            [
                '$inc' => [
                    'monthlyBreakdown.$.orders' => 1,
                    'monthlyBreakdown.$.revenue' => (float) $order->getTotalPrice(),
                ],
            ]
        );

        $existing = $this->collection->findOne([
            'menuId' => $menu->getId(),
            'monthlyBreakdown.month' => $month,
        ]);

        if (!$existing) {
            $this->collection->updateOne(
                ['menuId' => $menu->getId()],
                [
                    '$push' => [
                        'monthlyBreakdown' => [
                            'month' => $month,
                            'orders' => 1,
                            'revenue' => (float) $order->getTotalPrice(),
                        ],
                    ],
                ]
            );
        }
    }

    public function getMenuStats(): array
    {
        return $this->collection->find([], ['sort' => ['totalOrders' => -1]])->toArray();
    }

    public function getRevenueByMenuAndPeriod(?int $menuId, ?string $startMonth, ?string $endMonth): array
    {
        $stats = $menuId
            ? $this->collection->find(['menuId' => $menuId])->toArray()
            : $this->collection->find()->toArray();

        $results = [];

        foreach ($stats as $stat) {
            $revenue = 0;

            foreach ($stat['monthlyBreakdown'] ?? [] as $monthData) {
                $month = $monthData['month'];

                if (($startMonth === null || $month >= $startMonth) && ($endMonth === null || $month <= $endMonth)) {
                    $revenue += $monthData['revenue'];
                }
            }

            $results[] = [
                'menuId' => $stat['menuId'],
                'menuTitle' => $stat['menuTitle'],
                'revenue' => $revenue,
                'totalOrders' => $stat['totalOrders'] ?? 0,
            ];
        }

        return $results;
    }
}