<?php


namespace App\Helper;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCSV
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher,private EntityManagerInterface $entityManager)
    {}

    public function InsertUsers(UploadedFile $file): void
    {
        $handle = fopen($file->getPathname(),'r');

        fgetcsv($handle);

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
    }

}
