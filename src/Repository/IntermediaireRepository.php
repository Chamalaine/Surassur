<?php

namespace App\Repository;

use App\Entity\Intermediaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Intermediaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intermediaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intermediaire[]    findAll()
 * @method Intermediaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IntermediaireRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Intermediaire::class);
    }

    // /**
    //  * @return Intermediaire[] Returns an array of Intermediaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Intermediaire
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
