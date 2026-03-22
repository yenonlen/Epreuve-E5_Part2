<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('rv')
            ->leftJoin('rv.patient', 'p')->addSelect('p')
            ->orderBy('rv.date', 'ASC')
            ->addOrderBy('rv.heure', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
