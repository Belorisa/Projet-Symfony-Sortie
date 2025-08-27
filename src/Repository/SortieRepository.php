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
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($nPerPage)
            ->getQuery();

            return new Paginator($qB);
    }

    //fonction pour retourner les sorties par campus


    //fonction pour récupérer les 3 sorties du moment page accueil
    public function findSortiesByDate(Datetime $date): array {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'ASC')
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
            $qB->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        // Filter: past or future events
        $now = new \DateTime();

        $minDate = (new \DateTime())->modify('-1 month')->setTime(0, 0);
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

        return new Paginator($qB);
    }

    public function countFiltered(array $filters): int
    {
        $qB = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');
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
            $qB->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        // Filter: past or future events
        $now = new \DateTime();

        $minDate = (new \DateTime())->modify('-1 month')->setTime(0, 0);
        $qB->andWhere('s.dateHeureDebut >= :minDate')
            ->setParameter('minDate', $minDate);

        if (!empty($filters['passe'])) {
            $qB->andWhere('s.dateHeureDebut < :now');
        } else {
            $qB->andWhere('s.dateHeureDebut >= :now');
        }

        if (!empty($filters['apres']) && $filters['apres'] >= $minDate) {
            $qB->andWhere('s.dateHeureDebut >= :apres')
                ->setParameter('apres', $filters['apres']);
        }

        if (!empty($filters['avant'])) {
            $qB->andWhere('s.dateHeureDebut <= :avant')
                ->setParameter('avant', $filters['avant']);
        }

        $qB->setParameter('now', $now);

        return (int) $qB->getQuery()->getSingleScalarResult();
    }


    public function countAll(): int
    {
        $qB = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');

        $minDate = (new \DateTime())->modify('-1 month');
        $qB->andWhere('s.dateHeureDebut >= :minDate')
            ->setParameter('minDate', $minDate);

        return (int) $qB->getQuery()->getSingleScalarResult();
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
