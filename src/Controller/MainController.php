<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(SortieRepository $sortieRepository): Response
    {

        return $this->render('main/index.html.twig', [
            'moments' => $sortieRepository->findSortiesByDate(),
            'populaires' => $sortieRepository->findSortiesByPopular(),

        ]);
    }
}
