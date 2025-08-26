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
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($nPerPage)
            ->getQuery();

            return new Paginator($qB);
    }


    //fonction pour récupérer les 3 sorties du moment page accueil
    public function findSortiesByDate(Datetime $date): array {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    //fonction pour récupérer les 3 sorties les plus populaires page accueil
    public function findSeriesByNbParticipants(): array {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    //


    
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
