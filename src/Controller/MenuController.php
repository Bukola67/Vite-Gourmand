<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Attribute\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menu')]
    public function index(MenuRepository $menuRepository): Response
    {
        return $this->render('menu/index.html.twig', [
            'menus' => $menuRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/menus/{id}', name: 'app_menu_show', requirements: ['id' => '\\d+'])]
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', ['menu' => $menu]);
    }

    #[Route('/api/menus', name: 'app_menu_api_filter', methods: ['GET'])]
    public function filterApi(Request $request, MenuRepository $menuRepository): JsonResponse
    {
        $query = $request->query->all();
        $errors = [];
        $values = [];

        // Vérifier le type avant trim() : ?minPrice[]=10 est un tableau.
        foreach (['minPrice', 'maxPrice', 'theme', 'diet', 'minPersons'] as $name) {
            $raw = $query[$name] ?? '';
            if (!is_string($raw)) {
                $errors[$name] = 'Une valeur simple est attendue.';
                $values[$name] = '';
                continue;
            }
            $values[$name] = trim($raw);
        }

        $prices = ['minPrice' => null, 'maxPrice' => null];
        foreach (array_keys($prices) as $name) {
            if ($values[$name] === '' || isset($errors[$name])) {
                continue;
            }
            // DECIMAL(10, 2) : au plus 8 chiffres avant le point.
            if (!preg_match('/^[0-9]{1,8}(?:\\.[0-9]{1,2})?$/D', $values[$name])) {
                $errors[$name] = 'Prix attendu entre 0 et 99999999.99, avec au plus deux décimales (point).';
                continue;
            }
            $prices[$name] = (float) $values[$name];
        }

        if ($prices['minPrice'] !== null && $prices['maxPrice'] !== null
            && $prices['minPrice'] > $prices['maxPrice']) {
            $errors['maxPrice'] = 'Le prix maximum doit être supérieur ou égal au prix minimum.';
        }

        $minPersons = null;
        if ($values['minPersons'] !== '' && !isset($errors['minPersons'])) {
            $minPersons = filter_var($values['minPersons'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 2147483647],
            ]);
            if ($minPersons === false) {
                $errors['minPersons'] = 'Un entier entre 1 et 2147483647 est attendu.';
            }
        }

        // Les thèmes/régimes sont libres dans MenuType : pas de liste fermée inventée ici.
        foreach (['theme', 'diet'] as $name) {
            if (!isset($errors[$name]) && mb_strlen($values[$name]) > 100) {
                $errors[$name] = 'Saisissez au maximum 100 caractères.';
            }
        }

        if ($errors !== []) {
            return $this->json([
                'message' => 'Les filtres sont invalides.',
                'errors' => $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $menus = $menuRepository->findByFilters(
            $prices['minPrice'],
            $prices['maxPrice'],
            $values['theme'] !== '' ? $values['theme'] : null,
            $values['diet'] !== '' ? $values['diet'] : null,
            $minPersons,
        );

        return $this->json(array_map(fn (Menu $menu) => [
            'id' => $menu->getId(),
            'title' => $menu->getTitle(),
            'description' => $menu->getDescription(),
            'theme' => $menu->getTheme(),
            'diet' => $menu->getDiet(),
            'basePrice' => $menu->getBasePrice(),
            'minimumPersons' => $menu->getMinimumPersons(),
            'detailUrl' => $this->generateUrl('app_menu_show', ['id' => $menu->getId()]),
        ], $menus));
    }
}
