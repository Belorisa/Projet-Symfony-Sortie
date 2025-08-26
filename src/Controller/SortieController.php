<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'sortie')]

final class SortieController extends AbstractController
{
    #[Route('/list/{page}',
        name: '_list',
        requirements: ['page' => '\d+'],
        defaults: ['page' => 1],
        methods: ['GET']
)]
    public function list(): Response
    {
        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/creation', name: '_creation')]
    public function creationSortie(EntityManagerInterface $em,Request $request ): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user = $this->getUser();

            $sortie->setOrganisateur($user);
            $sortie->setEtat("Créée");

            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'l\'activité à bien été crée');
            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/sortie_form.html.twig', [
            'sortie_form' => $form,
        ]);
    }


}
