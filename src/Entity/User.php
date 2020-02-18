<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Discriminator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("email", message="L'adresse e-mail est déjà utilisé")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @Groups({"user", "user:short"})
     * @ORM\Column(type="integer")
     * @SWG\Property(description="Identifiant unique")
     */
    private $id;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Assert\Email
     * @SWG\Property(description="Email")
     */
    private $email;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="simple_array")
     * @SWG\Property(description="Rôles")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank
     * @SWG\Property(description="Mot de passe")
     */
    private $password;
    private $tmpPassword;

    /**
     * @Groups({"user", "user:short"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @SWG\Property(description="Prénom")
     */
    private $firstname;

    /**
     * @Groups({"user", "user:short"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @SWG\Property(description="Nom")
     */
    private $lastname;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(description="Numéro de téléphone")
     */
    private $phone;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="datetime")
     * @SWG\Property(description="Date de création")
     */
    private $createdAt;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="datetime")
     * @SWG\Property(description="Date de mise à jour")
     */
    private $updatedAt;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="boolean")
     * @SWG\Property(description="État")
     */
    private $enabled;

    /**
     * @Groups({"user"})
     * @ORM\Column(type="integer")
     * @SWG\Property(description="Les points")
     */
    private $points;

    /**
     * @Groups({"user_reset_password"})
     * @ORM\Column(type="string", length=5, nullable=true)
     * @SWG\Property(description="Code de redéfinition du mot de passe")
     */
    private $resetPasswordCode;

    /**
     * @Groups({"user_reset_password"})
     * @ORM\Column(type="datetime", nullable=true)
     * @SWG\Property(description="Date d'expiration du code de redéfinition du mot de passe")
     */
    private $resetPasswordExpireAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserToken", mappedBy="user", orphanRemoval=true)
     * @SWG\Property(description="Tokens d'accès de l'utilisateur")
     */
    private $token;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->enabled = true;
        $this->roles = ['ROLE_USER'];
        $this->token = new ArrayCollection();
        $this->points = 0;

    }

    public function getFullName(): string
    {
        return $this->getLastname().' '.$this->getFirstname();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setRole($role)
    {
      $this->roles[] = $role;
      return $this;
    }

    public function hasRole(string $role): bool
    {
      return in_array($role, $this->getRoles());
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getTmpPassword(): ?String
    {
        return $this->tmpPassword;
    }

    public function setTmpPassword(?string $password): self
    {
        $this->tmpPassword = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled === true;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getResetPasswordCode(): ?string
    {
        return $this->resetPasswordCode;
    }

    public function setResetPasswordCode(?string $code): self
    {
        $this->resetPasswordCode = $code;

        return $this;
    }

    public function getResetPasswordExpireAt(): ?\DateTimeInterface
    {
        return $this->resetPasswordExpireAt;
    }

    public function setResetPasswordExpireAt(?\DateTimeInterface $expireAt): self
    {
        $this->resetPasswordExpireAt = $expireAt;

        return $this;
    }

    public function getToken(): Collection
    {
        return $this->token;
    }

    public function addToken(UserToken $token): self
    {
        if (!$this->token->contains($token)) {
            $this->token[] = $token;
            $token->setUser($this);
        }

        return $this;
    }

    public function removeToken(UserToken $token): self
    {
        if ($this->token->contains($token)) {
            $this->token->removeElement($token);
            // set the owning side to null (unless already changed)
            if ($token->getUser() === $this) {
                $token->setUser(null);
            }
        }

        return $this;
    }

    public function clearToken(): self
    {
        $this->token  = [];
        return $this;
    }

    /** @ORM\PrePersist */
    /** @ORM\PreUpdate */
    public function prePersistHandler()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function __toString()
    {
        return "{$this->lastname} {$this->firstname}";
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;
        return $this;
    }
}
