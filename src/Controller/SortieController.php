<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
    public function list(SortieRepository $sortieRepository, int $page, ParameterBagInterface $parameters): Response
    {
        $nbPerPage = $parameters->get('sortie')['nb_max'];
        $offset = ($page - 1) * $nbPerPage;
        //tableau de critères de requete
        $criterias = [
            //'isPublished' => true
        ];

        $sorties = $sortieRepository->findAllSorties($nbPerPage, $offset);


        $total =$sortieRepository->count($criterias);
        $totalPages = ceil($total/$nbPerPage);

        return $this->render('sortie/list.html.twig', [
                'sorties' => $sorties,
                'page' => $page,
                'total_pages' => $totalPages,
                ]
        );
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
