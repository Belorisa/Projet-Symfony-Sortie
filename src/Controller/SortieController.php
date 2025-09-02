<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\SortieType;
use App\Form\UserType;
use App\Helper\PlaceRestante;
use App\Message\ReminderMessage;
use App\MessageHandler\SendReminderMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Mime\Email;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user = $this->getUser();

            $sortie->setOrganisateur($user);
            $sortie->setEtat("OUVERTE");

            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'l\'activité a bien été créée');
            return $this->redirectToRoute('sortie_list');
        }
        else{
            if($form->isSubmitted())
            {
                $this->addFlash('error','Please fix the error in the form');
            }
        }
        return $this->render('sortie/sortie_form.html.twig', [
            'sortie_form' => $form,
        ]);
    }

    #[Route('/detail/{id}', name: '_detail')]
    public function sortieDetail(Sortie $sortie ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');


        $listUsers = $sortie->getUsers();
        $placeRestante = $sortie->getNbInscriptionMax() - count($listUsers);

        if (!$sortie->getOrganisateur()) {
            $this->addFlash('error', 'Cette sortie n’a pas d’organisateur défini.');
            return $this->redirectToRoute('sortie_list');
        }


        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'listUsers' => $listUsers,
            'placeRestante' => $placeRestante,

        ]);
    }

    #[Route('/inscription/{id}', name: '_inscription')]
    public function sortieInscription(Sortie $sortie,EntityManagerInterface $em,MailerInterface $mailer,MessageBusInterface $bus): Response
    {

        $listUsers = $sortie->getUsers();
        $placeRestante = $sortie->getNbInscriptionMax() - count($listUsers);


        if($placeRestante == 1 && $sortie->getEtat() != "CLOTUREE")
        {
            $sortie->setEtat("CLOTUREE");
            $em->persist($sortie);
            $em->flush();
        }
        else{
            $this->addFlash('error','Désolé cette sortie est complète');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();
        if($sortie->getEtat()=="OUVERTE" && $sortie->getDateLimiteInscription()>new \DateTime())
        {
            $sortie->addUser($user);

            $email = (new Email())
                ->from('no-reply@sortie.fr')
                ->to($user->getEmail())
                ->subject('Confirmation d\'inscription à une sortie')
                ->html($this->renderView('emails/inscription.html.twig', [
                    'user' => $user,
                    'sortie' => $sortie,
                ]));

            $mailer->send($email);

            $eventStart = $sortie->getDateHeureDebut();
            $delayInSeconds = $eventStart->getTimestamp() - (new \DateTime())->getTimestamp() - (48*3600);
            $delayInMs = max(0,$delayInSeconds*1000);

            $bus->dispatch(new ReminderMessage($sortie->getId(),$user->getId()),
            [
                new DelayStamp($delayInMs)
            ]);

            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'L\'inscription a bien été prise en compte');
        }

        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);
    }

    #[Route('/deinscription/{id}', name: '_deinscription')]
    public function sortieDeinscription(Sortie $sortie,EntityManagerInterface $em,MailerInterface $mailer): Response
    {


        $user = $this->getUser();
        if($sortie->getEtat()!="EN COURS" && $sortie->getDateLimiteInscription()>new \DateTime())
        {
            $email = (new Email())
                ->from('no-reply@sortie.fr')
                ->to($user->getEmail())
                ->subject('Confirmation de désinscription a une sortie')
                ->html($this->renderView('emails/désinscription.html.twig', [
                    'user' => $user,
                    'sortie' => $sortie,
                ]));

            $mailer->send($email);

            $sortie->removeUser($user);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'Vous êtes bien désinscrit');
            return $this->redirectToRoute('sortie_detail', [
                'id' => $sortie->getId(),
            ]);
        }
        $this->addFlash('error', 'Cette action ne peut être effectuée');
        return $this->redirectToRoute('sortie_detail', [
            'id' => $sortie->getId(),
        ]);

    }



    #[Route('/update/{id}', name: '_update')]
    public function updateSortie(EntityManagerInterface $em,Request $request,Sortie $sortie ): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if ($this->getUser()->getId() != $sortie->getOrganisateur()->getId() && !$this->isGranted( 'ROLE_ADMIN')) {
            return $this->redirectToRoute('sortie_list');
        }

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user = $this->getUser();

            $sortie->setOrganisateur($user);
            $sortie->setEtat($sortie->getEtat());

            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'l\'activité a été modifiée avec succès');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/sortie_form.html.twig', [
            'sortie_form' => $form,
        ]);
    }

    #[Route('/annulation/{id}', name: '_annulation')]
    public function sortieAnnuler(Sortie $sortie, EntityManagerInterface $em,Request $request): Response
    {


        if ($this->getUser()->getId() != $sortie->getOrganisateur()->getId() && !$this->isGranted( 'ROLE_ADMIN')) {
                return $this->redirectToRoute('sortie_list');
        }

        $annul = $request->query->get("annul");

        if($annul)
        {
            dump($annul);
            $sortie->setInfoSortie($annul);
            $sortie->setEtat("ANNULEE");

            $em->persist($sortie);
            $em->flush();
            $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/annulation.html.twig', [
            'sortie' => $sortie,
        ]);
    }


}
