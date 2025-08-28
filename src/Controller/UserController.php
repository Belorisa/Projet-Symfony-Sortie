<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Helper\FileUploader;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function update(User $user, EntityManagerInterface $em,ParameterBagInterface $parameterBag, UserPasswordHasherInterface $userPasswordHasher, FileUploader $fileUploader, Request $request) : Response
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

                $em->flush();
                return $this->redirectToRoute('app_user', ['id' => $user->getId()]);
            }
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


    }

