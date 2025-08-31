<?php
namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $actif = 1;

    #[ORM\OneToMany(mappedBy: 'competence', targetEntity: UserCompetence::class, orphanRemoval: true)]
    private Collection $userCompetences;

    public function __construct()
    {
        $this->userCompetences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getActif(): ?int
    {
        return $this->actif;
    }

    public function setActif(int $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif === 1;
    }

    /**
     * @return Collection<int, UserCompetence>
     */
    public function getUserCompetences(): Collection
    {
        return $this->userCompetences;
    }

    public function addUserCompetence(UserCompetence $userCompetence): static
    {
        if (!$this->userCompetences->contains($userCompetence)) {
            $this->userCompetences->add($userCompetence);
            $userCompetence->setCompetence($this);
        }
        return $this;
    }

    public function removeUserCompetence(UserCompetence $userCompetence): static
    {
        if ($this->userCompetences->removeElement($userCompetence)) {
            if ($userCompetence->getCompetence() === $this) {
                $userCompetence->setCompetence(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}