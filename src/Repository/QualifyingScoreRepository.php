<?php

namespace App\Repository;

use App\Entity\QualifyingScore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QualifyingScore>
 *
 * @method QualifyingScore|null find($id, $lockMode = null, $lockVersion = null)
 * @method QualifyingScore|null findOneBy(array $criteria, array $orderBy = null)
 * @method QualifyingScore[]    findAll()
 * @method QualifyingScore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QualifyingScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualifyingScore::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(QualifyingScore $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(QualifyingScore $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByTeamAndTournament($team, $tournament): ?QualifyingScore
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.team = :team')
            ->andWhere('q.tournament = :tournament')
            ->setParameter('team', $team)
            ->setParameter('tournament', $tournament)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findTopTeamsByTournamentAndDivision(int $tournament, int $division): array
    {
        return $this->createQueryBuilder('qs')
            ->andWhere('qs.tournament = :tournament')
            ->andWhere('qs.team IN (
                SELECT t.id FROM App\Entity\Team t
                WHERE t.division = :division
            )')
            ->setParameter('tournament', $tournament)
            ->setParameter('division', $division)
            ->orderBy('qs.score', 'DESC')
            ->addOrderBy('qs.id', 'ASC')
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return QualifyingScore[] Returns an array of QualifyingScore objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QualifyingScore
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
