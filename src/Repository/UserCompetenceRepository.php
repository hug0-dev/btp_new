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

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByCompetence($competence): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.competence = :competence')
            ->setParameter('competence', $competence)
            ->getQuery()
            ->getResult();
    }

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
}