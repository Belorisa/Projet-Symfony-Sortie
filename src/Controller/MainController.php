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

        $allSorties = $sortieRepository->findUpcomingSorties();

        $sortiesByDate = $allSorties; // copy the array
        usort($sortiesByDate, fn($a, $b) => $a->getDateHeureDebut() <=> $b->getDateHeureDebut());
        $sortiesByDate = array_slice($sortiesByDate, 0, 3);

        $sortiesByPopular = $allSorties; // copy again
        usort($sortiesByPopular, fn($a, $b) =>
            ($a->getNbInscriptionMax() - count($a->getUsers()))
            <=>
            ($b->getNbInscriptionMax() - count($b->getUsers()))
        );
        $sortiesByPopular = array_slice($sortiesByPopular, 0, 3);

        return $this->render('main/index.html.twig', [
            'moments' => $sortiesByDate,
            'populaires' => $sortiesByPopular

        ]);
    }
}
