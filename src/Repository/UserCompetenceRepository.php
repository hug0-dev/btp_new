<?php

namespace App\Repository;

use App\Entity\UserCompetence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCompetence>
 */
class UserCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCompetence::class);
    }

    /**
     * Trouve les compétences d'un utilisateur
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs ayant une compétence donnée
     */
    public function findByCompetence($competence): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.competence = :competence')
            ->setParameter('competence', $competence)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur possède une compétence
     */
    public function hasUserCompetence($user, $competence): bool
    {
        $result = $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :user')
            ->andWhere('uc.competence = :competence')
            ->setParameter('user', $user)
            ->setParameter('competence', $competence)
            ->getQuery()
            ->getOneOrNullResult();
        
        return $result !== null;
    }

    //    /**
    //     * @return UserCompetence[] Returns an array of UserCompetence objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserCompetence
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}