<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'sortie')]

final class SortieController extends AbstractController
{
    #[Route('/', name: '_list')]
    public function list(): Response
    {
        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }


}
