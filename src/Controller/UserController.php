<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user/{id}', name: 'app_user')]
    public function index(User $user, Request $request, SortieRepository $sortieRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $isModified = false;

        //recuperer les sorties organisées par l'utilisateur
        $criterias = ['organisateur' => $user,];

        $orderBy = ['dateHeureDebut' => 'ASC'];

        $sortiesOrganisees = $sortieRepository->findBy(
            $criterias, $orderBy
            //['dateHeureDebut' => 'ASC'],
        );

        //recuperer les inscriptions de l'utilisateur




        return $this->render('user/index.html.twig', [
            'user_form' => $form,
            'isModified' => $isModified,
            'sortiesOrganisees' => $sortiesOrganisees
        ]);
    }

    #[Route('/user/update/{id}', name: 'app_user_update')]
    public function update(User $user, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, Request $request) : Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setAdministrateur(false);
            $user->setActif(true);

            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('app_user',['id'=>$user->getId()]);
        }


        $isModified = true;


        return $this->render('user/index.html.twig', [
           'user_form' => $form,
            'isModified' => $isModified,


        ]);

    }


}
