<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LieuController extends AbstractController
{
    #[Route('/lieu', name: 'app_lieu')]
    public function index(): Response
    {
        return $this->render('lieu/new.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    #[Route('/lieu/new', name: 'app_lieu_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            $this->addFlash('success', 'Un nouveau lieu a été créé');

            return $this->redirectToRoute('sortie_creation', [
                'nom' => $lieu->getNom(),
            ]);
        }

        return $this->render('lieu/new.html.twig', [
            'Lieu_form' => $form->createView(),
        ]);
    }

}

