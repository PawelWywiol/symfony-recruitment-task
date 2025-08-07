<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_users_list', ['page' => UsersController::DEFAULT_PAGE_INDEX]);
    }
}
