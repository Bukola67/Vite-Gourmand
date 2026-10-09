<?php
// src/Repository/MenuRepository.php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * @return Menu[]
     */
    public function findByFilters(
        ?float $minPrice,
        ?float $maxPrice,
        ?string $theme,
        ?string $diet,
        ?int $minPersons
    ): array {
        $qb = $this->createQueryBuilder('m');

        if ($minPrice !== null) {
            $qb->andWhere('m.basePrice >= :minPrice')->setParameter('minPrice', $minPrice);
        }
        if ($maxPrice !== null) {
            $qb->andWhere('m.basePrice <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }
        if ($theme) {
            $qb->andWhere('m.theme = :theme')->setParameter('theme', $theme);
        }
        if ($diet) {
            $qb->andWhere('m.diet = :diet')->setParameter('diet', $diet);
        }
        if ($minPersons !== null) {
            $qb->andWhere('m.minimumPersons >= :minPersons')->setParameter('minPersons', $minPersons);
        }

        return $qb->orderBy('m.id', 'ASC')->getQuery()->getResult();
    }
}