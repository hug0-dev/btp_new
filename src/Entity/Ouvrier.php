<?php

namespace App\Entity;

use App\Repository\OuvrierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OuvrierRepository::class)]
class Ouvrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom_ouvrier = null;

    #[ORM\Column(type: Types::JSON)] // Stocke plusieurs compétences
    private array $competences = [];

    #[ORM\Column(length: 50)]
    private ?string $role = null;


    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "ouvriers")]
    #[ORM\JoinColumn(onDelete: "SET NULL")] // L'équipe peut être null si supprimée
    private ?Equipe $equipe = null;

    #[ORM\OneToMany(mappedBy: 'ouvrier', targetEntity: Affectation::class, orphanRemoval: true)]
    private Collection $affectations;

    public function __construct()
    {
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomOuvrier(): ?string
    {
        return $this->nom_ouvrier;
    }

    public function setNomOuvrier(string $nom_ouvrier): self
    {
        $this->nom_ouvrier = $nom_ouvrier;
        return $this;
    }

    public function getCompetences(): array
    {
        return $this->competences;
    }

    public function setCompetences(array $competences): self
    {
        $this->competences = $competences;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): self
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setOuvrier($this);
        }
        return $this;
    }

    public function removeAffectation(Affectation $affectation): self
    {
        if ($this->affectations->removeElement($affectation)) {
            if ($affectation->getOuvrier() === $this) {
                $affectation->setOuvrier(null);
            }
        }
        return $this;
    }
}
