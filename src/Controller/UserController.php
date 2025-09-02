<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Helper\FileUploader;
use App\Helper\UserCSV;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            'sortiesOrganisees' => $sortiesOrganisees,
            'user' => $user,
        ]);
    }

    #[Route('/user/update/{id}', name: 'app_user_update')]
    public function update(User $user, EntityManagerInterface $em,ParameterBagInterface $parameterBag, UserPasswordHasherInterface $userPasswordHasher, FileUploader $fileUploader, Request $request,Security $security) : Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        if ($this->getUser()->getId() != $user->getId() ) {
            $this->addFlash('error', 'Désolé cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');

        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $plainPassword)
                );
            }
            else
            {
                $user->setPassword($user->getPassword());
            }
            $user->setAdministrateur(false);
            $user->setActif(true);

            $file = $form->get('photo')->getData();


            if ($file instanceof UploadedFile) {
                $dir = $parameterBag->get('sortie')['photos_directory'];

                // Delete the old photo if it exists
                $oldPhoto = $user->getPhoto();
                if ($oldPhoto && file_exists($dir . '/' . $oldPhoto)) {
                    unlink($dir . '/' . $oldPhoto);
                }

                // Upload the new photo
                $newPhotoName = $fileUploader->upload($file, 'user-photo', $dir);
                $user->setPhoto($newPhotoName);

            }
            if(!$this->isGranted('ROLE_ADMIN') ){ //Si l'utilisateur qui modifie le profil n'est pas administrateur
                $security->login($user);
            }
            $em->flush();
            return $this->redirectToRoute('app_user', ['id' => $user->getId()]);
        }
            $isModified = true;
            $sortiesOrganisees = $em->getRepository(Sortie::class)->findBy(['organisateur' => $user]);

            return $this->render('user/index.html.twig', [
                'user_form' => $form,
                'isModified' => $isModified,
                'sortiesOrganisees' => $sortiesOrganisees,
                'user' => $user,
            ]);

        }

    //gestion des utilisateurs par l'administrateur

    //rôle admin défini dans security.yaml
    #[Route('/admin/user_list', name: 'admin_user_list')]
    public function listUsers(Request $request, EntityManagerInterface $em): Response {

        if (!$this->isGranted('ROLE_ADMIN' )) {
            $this->addFlash('error', 'Désolé cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');

        }

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('user/user_list.html.twig', [
            'users' => $users
        ]);
    }

    //rôle admin défini dans security.yaml
    #[Route('/admin/user_desactiver/{id}',
        name: 'admin_user_desactiver',
        requirements: ['id' => '\d+'],
        methods: 'GET')]
    public function desactiverUser(User $user, EntityManagerInterface $em): Response {

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Désolé cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');

        }

        $user->setActif(false);
        $em->flush();

        //message de succès
        $this->addFlash('success', '✅ Utilisateur désactivé avec succès.');
        return $this->redirectToRoute('admin_user_list');
    }

    //rôle admin défini dans security.yaml
    #[Route('/admin/user_activer/{id}',
        name: 'admin_user_activer',
        requirements: ['id' => '\d+'],
        methods: 'GET')]
    public function activerUser(User $user, EntityManagerInterface $em): Response {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->addFlash('error', 'Désolé, cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');

        }

        $user->setActif(true);
        $em->flush();

        //message de succès
        $this->addFlash('success', '✅ Utilisateur activé avec succès.');
        return $this->redirectToRoute('admin_user_list');
    }

    //rôle admin défini dans security.yaml
    #[Route('/admin/user_delete/{id}',
        name: 'admin_user_delete',
        requirements: ['id' => '\d+'],
        methods: 'GET' )]
    public function deleteUser(User $user, EntityManagerInterface $em, Request $request): Response {


        if (!$this->isGranted( 'ROLE_ADMIN')) {
            $this->addFlash('error', 'Désolé, cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');

        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('token')))
        {
            $em->remove($user);
            $em->flush();

            //message de succès
            $this->addFlash('success', '✅ Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('danger', '⛔ ⚠ Suppression impossible');

        }

            return $this->redirectToRoute('admin_user_list');
    }

    #[Route('/admin/userlist', name: 'admin_user_userlist')]
    public function addUsersList(Request $request,UserCSV $CSV) : Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Désolé cette action n\'est pas autorisée')  ;
            return $this->redirectToRoute('sortie_list');
        }

        $listusers= $request->files->get("listusers");

        $CSV->InsertUsers($listusers);

        return $this->redirectToRoute('admin_user_list');

    }

    }

