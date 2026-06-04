<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menu')]
    public function index(): Response
    {
        $menus = [
            [
                'id' => 1,
                'titre' => 'Menu Noël',
                'description' => 'Un menu festif pour les repas de fin d’année.',
                'theme' => 'Noël',
                'prix' => 25,
                'personnes' => 4
            ],
            [
                'id' => 2,
                'titre' => 'Menu Pâques',
                'description' => 'Un menu gourmand pour vos repas de Pâques.',
                'theme' => 'Pâques',
                'prix' => 22,
                'personnes' => 4
            ],
            [
                'id' => 3,
                'titre' => 'Menu Classique',
                'description' => 'Une formule simple et savoureuse pour tous vos événements.',
                'theme' => 'Classique',
                'prix' => 18,
                'personnes' => 2
            ],
        ];

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/menus/{id}', name: 'app_menu_show')]
    public function show(int $id): Response
    {
        $menus = [
            1 => [
                'id' => 1,
                'titre' => 'Menu Noël',
                'description' => 'Un menu festif pour les repas de fin d’année.',
                'theme' => 'Noël',
                'prix' => 25,
                'personnes' => 4,
                'conditions' => 'Commande à effectuer au moins 7 jours avant la prestation.',
                'regime' => 'Classique'
            ],
            2 => [
                'id' => 2,
                'titre' => 'Menu Pâques',
                'description' => 'Un menu gourmand pour vos repas de Pâques.',
                'theme' => 'Pâques',
                'prix' => 22,
                'personnes' => 4,
                'conditions' => 'Commande à effectuer au moins 5 jours avant la prestation.',
                'regime' => 'Végétarien'
            ],
            3 => [
                'id' => 3,
                'titre' => 'Menu Classique',
                'description' => 'Une formule simple et savoureuse pour tous vos événements.',
                'theme' => 'Classique',
                'prix' => 18,
                'personnes' => 2,
                'conditions' => 'Commande à effectuer 48h avant la prestation.',
                'regime' => 'Classique'
            ],
        ];

        if (!isset($menus[$id])) {
            throw $this->createNotFoundException('Menu introuvable');
        }

        return $this->render('menu/show.html.twig', [
            'menu' => $menus[$id],
        ]);
    }
}