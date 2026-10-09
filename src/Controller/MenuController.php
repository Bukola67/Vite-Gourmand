<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menu')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('/menus/{id}', name: 'app_menu_show', requirements: ['id' => '\d+'])]
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/api/menus', name: 'app_menu_api_filter', methods: ['GET'])]
public function filterApi(Request $request, MenuRepository $menuRepository): JsonResponse
    {
        $minPrice   = $request->query->get('minPrice');
        $maxPrice   = $request->query->get('maxPrice');
        $theme      = $request->query->get('theme');
        $diet       = $request->query->get('diet');
        $minPersons = $request->query->get('minPersons');

        $menus = $menuRepository->findByFilters(
            ($minPrice !== null && $minPrice !== '') ? (float) $minPrice : null,
            ($maxPrice !== null && $maxPrice !== '') ? (float) $maxPrice : null,
            $theme ?: null,
            $diet ?: null,
            ($minPersons !== null && $minPersons !== '') ? (int) $minPersons : null,
        );

        $data = array_map(fn (Menu $menu) => [
            'id'             => $menu->getId(),
            'title'          => $menu->getTitle(),
            'description'    => $menu->getDescription(),
            'theme'          => $menu->getTheme(),
            'diet'           => $menu->getDiet(),
            'basePrice'      => $menu->getBasePrice(),
            'minimumPersons' => $menu->getMinimumPersons(),
            'detailUrl'      => $this->generateUrl('app_menu_show', ['id' => $menu->getId()]),
        ], $menus);

        return $this->json($data);
    }



}