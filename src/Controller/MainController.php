<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(): Response
    {
        dump(date_default_timezone_get());
        dump(new \DateTime());
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
