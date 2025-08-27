<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use App\Form\UserType;
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
        methods: ['GET','POST']
    )]
    public function list(SortieRepository $sortieRepository, int $page, ParameterBagInterface $parameters,EntityManagerInterface $em,Request $request): Response
    {
        $nbPerPage = $parameters->get('sortie')['nb_max'];
        $offset = ($page - 1) * $nbPerPage;

        $list = $em->getRepository(Site::class)->findAll();
        $sorties = $sortieRepository->findAllSorties($nbPerPage, $offset);

        $orga = $request->query->get("orga");
        $site = $request->query->get("site");
        $contents = $request->query->get("contents");
        $inscrit = $request->query->get("inscrit");
        $pasinscrit = $request->query->get("pasinscrit");
        $passe = $request->query->get("passe");
        $avant = $request->query->get("avant");
        $apres = $request->query->get("apres");
        $user = $this->getUser();

        $total = $sortieRepository->countAll();

        if($orga || $apres|| $avant|| $site || $contents || $inscrit || $pasinscrit || $passe){
            $filters = [
                'site' => $site,
                'contents' => $contents,
                'orga' => $orga,
                'inscrit' => $inscrit,
                'pasinscrit' => $pasinscrit,
                'passe' => $passe,
                'user' => $user,
                'avant' => $avant ? new \DateTime($avant) : null,
                'apres' => $apres ? new \DateTime($apres) : null,
            ];
            $sorties = $sortieRepository->findSortie($filters, $nbPerPage, $offset);
            $total = $sortieRepository->countFiltered($filters);
        }



        $totalPages = ceil($total/$nbPerPage);

        return $this->render('sortie/list.html.twig', [
                'sorties' => $sorties,
                'page' => $page,
                'totalPages' => $totalPages,
                'sites' => $list,
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

    #[Route('/detail/{id}', name: '_detail')]
    public function sortieDetail(Sortie $sortie, EntityManagerInterface $em): Response
    {
        $sortieAffichage = $em->getRepository(Sortie::class)->find($sortie->getId());
        $listUsers = $sortieAffichage->getUsers();

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortieAffichage,
            'listUsers' => $listUsers,

        ]);
    }

    #[Route('/inscription/{id}', name: '_inscription')]
    public function sortieInscription(Sortie $sortie,EntityManagerInterface $em): Response
    {


        $user = $this->getUser();
        if($sortie->getEtat()=="OUVERTE" && $sortie->getDateLimiteInscription()>new \DateTime())
        {
            $sortie->addUser($user);
            $em->persist($sortie);
            $em->flush();
        }




        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);
    }

    #[Route('/deinscription/{id}', name: '_deinscription')]
    public function sortieDeinscription(Sortie $sortie,EntityManagerInterface $em): Response
    {


        $user = $this->getUser();
        if($sortie->getEtat()!="EN COURS" && $sortie->getDateLimiteInscription()>new \DateTime())
        {
            $sortie->removeUser($user);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Vous êtes bien inscrit');
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }
        $this->addFlash('error', 'Cette action ne peut être effectué');
        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);

    }


}
