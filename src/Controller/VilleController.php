<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VilleController extends AbstractController
{
    #[Route('/ville', name: 'app_ville')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        return $this->render('ville/new.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    #[Route('/ville/nouvelle', name: 'app_ville_nouvelle')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');


        $ville = new Ville();
        $form = $this->createForm(villeType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            $this->addFlash('success', 'Une nouvelle ville a été créée');

            return $this->redirectToRoute('app_lieu_new', [
                'nom' => $ville->getNom(),
            ]);
        }

        return $this->render('ville/nouvelle.html.twig', [
            'ville_form' => $form->createView(),
        ]);
    }
}
