<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * Recherche des patients par nom ou prénom (insensible à la casse).
     * Utilisé par le scénario nominal US01 — étape 2.
     */
    public function searchByNom(string $query): array
    {
        $q = trim($query);

        if ($q === '') {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->where('LOWER(p.nom) LIKE LOWER(:q) OR LOWER(p.prenom) LIKE LOWER(:q)')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
