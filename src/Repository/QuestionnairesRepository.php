<?php

namespace App\Repository;

use App\Entity\Questionnaires;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Questionnaires|null find($id, $lockMode = null, $lockVersion = null)
 * @method Questionnaires|null findOneBy(array $criteria, array $orderBy = null)
 * @method Questionnaires[]    findAll()
 * @method Questionnaires[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionnairesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaires::class);
    }

    // /**
    //  * @return Questionnaires[] Returns an array of Questionnaires objects
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
    public function findOneBySomeField($value): ?Questionnaires
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
