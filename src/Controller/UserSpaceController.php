<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserSpaceController extends AbstractController
{
    #[Route('/compte', name: 'app_user_space')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('user_space/index.html.twig');
    }
}