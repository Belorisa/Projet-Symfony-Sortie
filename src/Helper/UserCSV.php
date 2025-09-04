<?php


namespace App\Helper;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserCSV
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher,private EntityManagerInterface $entityManager)
    {}

    public function InsertUsers(UploadedFile $file, SessionInterface $session): void
    {
        $handle = fopen($file->getPathname(),'r');
        fgetcsv($handle);

        $doublons = []; //tableau pour stockage des doublons ignorés pendant l'import

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if(count($row)<5) {
                continue;
            }
            $row = array_map('trim', $row);
            [$email,$nom,$prenom,$telephone,$pseudo,$campusName] = $row;
            dump($email);
            if (!str_ends_with($email, '@campus-eni.fr')) {
                continue;
            }

            //verification si user existe déjà ds BDD
            $userExist = $this->entityManager
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);
            if($userExist) {
                $doublons[] = $email;
                continue; //ignore si l'email existe déjà ds bdd
            }


            $campus = $this->entityManager->getRepository(Site::class)->findOneBy(['nom' => $campusName]);
            if (!$campus) {
                continue; // skip if campus does not exist
            }

            $user = new User();
            $user->setCampus($campus);
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setTelephone($telephone);
            $user->setPseudo($pseudo);
            $user->setPassword($this->passwordHasher->hashPassword($user,'123456'));
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $user->setAdministrateur(false);
            $this->entityManager->persist($user);
        }
        fclose($handle);
        $this->entityManager->flush();

        /*
        //affichage des doublons ignorés
        if(!empty($doublons)) {
        dd($doublons);
            echo "Liste des utilisateurs ignorés : \n";
            foreach ($doublons as $doublon) {
                printf("L'utilisateur avec l'adresse mail : %s a été ignoré \n", $doublon);
            }
        }
        */

        //affichage des doublons ignorés
        if(!empty($doublons)) {
            $message = "⚠ Import terminé - doublon(s) détecté(s) <br>Liste des utilisateurs en doublon ignorés : <br>";
            foreach ($doublons as $doublon) {
                $message .= "L'utilisateur avec l'adresse mail : ". $doublon .  " a été ignoré <br>";
                $session->getFlashBag()->add('warning', $message);
            }
        } else {
            $message = "✅ Import terminé avec succès - Aucun doublon détecté";
            $session->getFlashBag()->add('success', $message);

        }


    }

}
