<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ReviewRepository $reviewRepository): Response
    {
        $reviews = $reviewRepository->findBy(
            ['isValidated' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('home/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }
}