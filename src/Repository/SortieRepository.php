<?php

namespace App\Repository;

use App\Entity\Sortie;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }



    public function findAllSorties(int $nPerPage, int $offset): Paginator {
        $qB = $this->createQueryBuilder('s')
            ->leftJoin('s.organisateur', 'o')->addSelect('o')
            ->leftJoin('s.users', 'u')->addSelect('u')
            ->leftJoin('s.site', 'site')->addSelect('site')
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($nPerPage);

            return new Paginator($qB,true);
    }

    public function countUserForSortie(int $sortieId): int {
        return $this->createQueryBuilder('s')
            ->select('COUNT(u)')
            ->join('s.users', 'u')
            ->where('s.id = :id')
            ->setParameter('id', $sortieId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //fonction pour retourner les sorties par campus


    //fonction pour récupérer les 3 sorties du moment page accueil
//    public function findSortiesByDate(): array {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.dateHeureDebut >= :now')
//            ->andWhere('s.etat = \'OUVERTE\'')
//            ->setParameter('now', new DateTime())
//            ->orderBy('s.dateHeureDebut', 'ASC')
//            ->setFirstResult(0)
//            ->setMaxResults(3)
//            ->getQuery()
//            ->getResult();
//    }
//
//    //fonction pour récupérer les 3 sorties OUVERTES les plus populaires page accueil
//    public function findSortiesByPopular(): array
//    {
//        $sortiesOuvertes = $this->createQueryBuilder('s')
//            ->leftJoin('s.users', 'u')
//            ->addSelect('s.nbInscriptionMax - count(u) AS HIDDEN placeRestante')
//            ->andWhere('s.dateHeureDebut >= :now')
//            ->andWhere('s.etat = :etat')
//            ->groupBy('s.id')
//            ->orderBy('placeRestante', 'ASC')
//            ->setMaxResults(3)
//            ->setParameter('now', new \DateTime())
//            ->setParameter('etat', 'OUVERTE');
//
//        $results = $sortiesOuvertes->getQuery()->getResult();
//
//        $sortiesWithInfo = [];
//        foreach ($results as $sortie) {
//            $nbUsers = count($sortie->getUsers());
//            $sortiesWithInfo[] = [
//                'sortie' => $sortie,
//                'nbUsers' => $nbUsers,
//                'placesRestantes' => $sortie->getNbInscriptionMax() - $nbUsers,
//            ];
//        }
//
//        return $sortiesWithInfo;
//
//
//    }

    public function findUpcomingSorties(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.users', 'u')
            ->addSelect('u')
            ->andWhere('s.dateHeureDebut >= :now')
            ->andWhere('s.etat = :etat')
            ->setParameter('now', new \DateTime())
            ->setParameter('etat', 'OUVERTE')
            ->getQuery()
            ->getResult();

    }


    public function findSortie(array $filters,int $nPerPage, int $offset): Paginator
    {
        $qB = $this->createQueryBuilder('s')
            ->setFirstResult($offset)
            ->setMaxResults($nPerPage);

        if (!empty($filters['site'])) {
            $qB->andWhere('s.site = :site')
                ->setParameter('site', $filters['site']);
        }
        if (!empty($filters['contents'])) {
            $qB->andWhere('s.nom LIKE :contents')
                ->setParameter('contents', '%' . $filters['contents'] . '%');
        }

        $user = $filters['user'] ?? null;

        // Filter: user is the organizer
        if (!empty($filters['orga']) && $user) {
            $qB->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // Filter: user is registered in the sortie
        if (!empty($filters['inscrit']) && $user) {
            $qB ->innerJoin('s.users', 'uInscrit')
                ->andWhere('uInscrit = :user')
                ->setParameter('user', $user);
        }

        // Filter: past or future events
        $now = new \DateTime();

        $minDate = ((clone $now)->modify('-1 month')->setTime(0, 0));
        $qB->andWhere('s.dateHeureDebut >= :minDate')
            ->setParameter('minDate', $minDate);

        if (!empty($filters['passe'])) {
            $qB->andWhere('s.dateHeureDebut < :now');
        } else {
            $qB->andWhere('s.dateHeureDebut >= :now');
        }

        if (!empty($filters['apres'])) {
            $qB->andWhere('s.dateHeureDebut >= :apres')
                ->setParameter('apres', $filters['apres']);
        }

        if (!empty($filters['avant'])) {
            $qB->andWhere('s.dateHeureDebut <= :avant')
                ->setParameter('avant', $filters['avant']);
        }

        $qB->setParameter('now', $now);

        return new Paginator($qB,true);
    }


    public function findDetail(int $id): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.users', 'u')->addSelect('u')
            ->leftJoin('s.organisateur', 'o')->addSelect('o')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findDetailWithCount(int $id): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.users', 'u')->addSelect('u')               // eager-load users
            ->leftJoin('s.organisateur', 'o')->addSelect('o')       // eager-load organizer
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    
    //    /**
    //     * @return Sortie[] Returns an array of Sortie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Sortie
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
