<?php

namespace App\Repository;

use App\Entity\Attenders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attenders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attenders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attenders[]    findAll()
 * @method Attenders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attenders::class);
    }

    // /**
    //  * @return Attenders[] Returns an array of Attenders objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attenders
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
