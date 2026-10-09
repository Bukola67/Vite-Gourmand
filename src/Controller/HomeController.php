<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ReviewRepository $reviewRepository, MenuRepository $menuRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'reviews' => $reviewRepository->findValidatedReviews(),
            'menus' => $menuRepository->findBy(
                [],
                ['id' => 'DESC'],
                5
            ),
        ]);
    }
}