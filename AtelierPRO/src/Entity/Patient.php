<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[ORM\Table(name: 'patient')]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'integer')]
    private ?int $age = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $sexe = null; // 'M' ou 'F'

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $principalePathologie = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $stade = null;

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: RendezVous::class)]
    private Collection $rendezVous;

    public function __construct()
    {
        $this->rendezVous = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getNomComplet(): string
    {
        return $this->nom . ' ' . $this->prenom;
    }

    public function getCivilite(): string
    {
        return $this->sexe === 'F' ? 'Mme' : 'M.';
    }

    public function getAge(): ?int { return $this->age; }
    public function setAge(int $age): self { $this->age = $age; return $this; }

    public function getSexe(): ?string { return $this->sexe; }
    public function setSexe(string $sexe): self { $this->sexe = $sexe; return $this; }

    public function getPrincipalePathologie(): ?string { return $this->principalePathologie; }
    public function setPrincipalePathologie(?string $p): self { $this->principalePathologie = $p; return $this; }

    public function getStade(): ?int { return $this->stade; }
    public function setStade(?int $stade): self { $this->stade = $stade; return $this; }

    public function getRendezVous(): Collection { return $this->rendezVous; }
}
