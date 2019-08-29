<?php

namespace App\Repository;

use App\Entity\Assure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Assure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Assure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Assure[]    findAll()
 * @method Assure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Assure::class);
    }


    public function searchAssure($query,$id)
    {
        return $this->createQueryBuilder('a')
        ->andWhere('a.nom LIKE :q')
        ->orWhere('a.prenom LIKE :q')
        ->andWhere('a.intermediaire = :id')
        ->setParameter('q',$query)
        ->setParameter('id',$id)
        ->getQuery()
        ->getResult();
    }

    public function doublonAssure($assure)
    {
        return $this->createQueryBuilder('a')
        ->andWhere('a.nom LIKE :n')
        ->andWhere('a.prenom LIKE :p')
        ->andWhere('a.intermediaire = :i')
        ->andWhere('a.dateNaissance = :d')
        ->setParameter('p',$assure->getPrenom())
        ->setParameter('n',$assure->getNom())
        ->setParameter('i',$assure->getIntermediaire())
        ->setParameter('d',$assure->getdateNaissance())
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function findByIntermediaire($id)
    {
        return $this->createQueryBuilder('a')
        ->where('a.intermediaire = :id')
        ->setParameter('id', $id)
        ->getQuery()
        ->getResult();
    }

    // /**
    //  * @return Assure[] Returns an array of Assure objects
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
    public function findOneBySomeField($value): ?Assure
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
