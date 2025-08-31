<?php
namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_equipe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'equipesDirectes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $chefEquipe = null;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: User::class)]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: Affectation::class, orphanRemoval: true)]
    private Collection $affectations;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->affectations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEquipe(): ?string
    {
        return $this->nom_equipe;
    }

    public function setNomEquipe(string $nom_equipe): static
    {
        $this->nom_equipe = $nom_equipe;
        return $this;
    }

    public function getChefEquipe(): ?User
    {
        return $this->chefEquipe;
    }

    public function setChefEquipe(?User $chefEquipe): static
    {
        $this->chefEquipe = $chefEquipe;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setEquipe($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            if ($user->getEquipe() === $this) {
                $user->setEquipe(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Affectation>
     */
    public function getAffectations(): Collection
    {
        return $this->affectations;
    }

    public function addAffectation(Affectation $affectation): static
    {
        if (!$this->affectations->contains($affectation)) {
            $this->affectations->add($affectation);
            $affectation->setEquipe($this);
        }
        return $this;
    }

    public function removeAffectation(Affectation $affectation): static
    {
        if ($this->affectations->removeElement($affectation)) {
            if ($affectation->getEquipe() === $this) {
                $affectation->setEquipe(null);
            }
        }
        return $this;
    }

    public function getNombre(): int
    {
        return $this->users->count();
    }

    /**
     * Retourne toutes les compétences de l'équipe (union des compétences de tous les membres)
     */
    public function getCompetences(): array
    {
        $competences = [];
        foreach ($this->users as $user) {
            foreach ($user->getUserCompetences() as $userCompetence) {
                $competence = $userCompetence->getCompetence();
                if ($competence && $competence->isActif()) {
                    $competences[$competence->getId()] = $competence->getNom();
                }
            }
        }
        return array_values($competences);
    }

    public function __toString(): string
    {
        return $this->nom_equipe ?? '';
    }
}