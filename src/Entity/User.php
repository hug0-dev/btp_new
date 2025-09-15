<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Equipe $equipe = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCompetence::class, orphanRemoval: true)]
    private Collection $userCompetences;

    #[ORM\OneToMany(mappedBy: 'chefChantier', targetEntity: Chantier::class)]
    private Collection $chantiersDirectes;

    #[ORM\OneToMany(mappedBy: 'chefEquipe', targetEntity: Equipe::class)]
    private Collection $equipesDirectes;

    public function __construct()
    {
        $this->userCompetences = new ArrayCollection();
        $this->chantiersDirectes = new ArrayCollection();
        $this->equipesDirectes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): static
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getUserCompetences(): Collection
    {
        return $this->userCompetences;
    }

    public function addUserCompetence(UserCompetence $userCompetence): static
    {
        if (!$this->userCompetences->contains($userCompetence)) {
            $this->userCompetences->add($userCompetence);
            $userCompetence->setUser($this);
        }
        return $this;
    }

    public function removeUserCompetence(UserCompetence $userCompetence): static
    {
        if ($this->userCompetences->removeElement($userCompetence)) {
            if ($userCompetence->getUser() === $this) {
                $userCompetence->setUser(null);
            }
        }
        return $this;
    }

    public function getChantiersDirectes(): Collection
    {
        return $this->chantiersDirectes;
    }

    public function getEquipesDirectes(): Collection
    {
        return $this->equipesDirectes;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function addChantiersDirecte(Chantier $chantiersDirecte): static
    {
        if (!$this->chantiersDirectes->contains($chantiersDirecte)) {
            $this->chantiersDirectes->add($chantiersDirecte);
            $chantiersDirecte->setChefChantier($this);
        }

        return $this;
    }

    public function removeChantiersDirecte(Chantier $chantiersDirecte): static
    {
        if ($this->chantiersDirectes->removeElement($chantiersDirecte)) {
            if ($chantiersDirecte->getChefChantier() === $this) {
                $chantiersDirecte->setChefChantier(null);
            }
        }

        return $this;
    }

    public function addEquipesDirecte(Equipe $equipesDirecte): static
    {
        if (!$this->equipesDirectes->contains($equipesDirecte)) {
            $this->equipesDirectes->add($equipesDirecte);
            $equipesDirecte->setChefEquipe($this);
        }

        return $this;
    }

    public function removeEquipesDirecte(Equipe $equipesDirecte): static
    {
        if ($this->equipesDirectes->removeElement($equipesDirecte)) {
            if ($equipesDirecte->getChefEquipe() === $this) {
                $equipesDirecte->setChefEquipe(null);
            }
        }

        return $this;
    }
}