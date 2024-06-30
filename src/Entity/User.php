<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déja avec cette adresse mail ')]
#[ApiResource()]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Regex( 
        pattern : "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/",
        message : "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial."
        )]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:3)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:3)]
    private ?string $lastname = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:10)]
    private ?string $NumeroTel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $Adresse = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:5)]
    #[Assert\Length(max:5)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $Ville = null;

    #[ORM\Column]
    private ?string $matricule = null;

    #[ORM\Column(type:'boolean')]
    private $isVerified = false; 

    #[ORM\ManyToOne(targetEntity: self::class , inversedBy:"clients")]
    #[ORM\JoinColumn(name:"employer_id",referencedColumnName:"id",nullable:true)]
    private ?self $employer = null;
    
    #[ORM\OneToMany(targetEntity:"App\Entity\Order",mappedBy:"user")]
    private $orders;

    #[ORM\OneToMany(mappedBy: 'employer', targetEntity: self::class)]
    // #[ORM\JoinColumn(name:"client_id",referencedColumnName:"id",nullable:true)]
    private Collection $clients;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->sent = new ArrayCollection();
        $this->received = new ArrayCollection();
    }

    #[ORM\Column(name: "employer_id", type: "integer" , nullable: true)]
    private ?int $employerId ; 

    
    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Messages::class, orphanRemoval: true)]
    private Collection $sent;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Messages::class, orphanRemoval: true)]
    private Collection $received;

    // Ajoutez ensuite les méthodes set et get correspondantes
    public function setEmployerId(?int $employerId): self
    {
        $this->employerId = $employerId;
        return $this;
    }

    public function getEmployerId(): ?int
    {
        return $this->employerId;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getNumeroTel(): ?string
    {
        return $this->NumeroTel;
    }

    public function setNumeroTel(string $NumeroTel): static
    {
        $this->NumeroTel = $NumeroTel;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->Adresse;
    }

    public function setAdresse(string $Adresse): static
    {
        $this->Adresse = $Adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->Ville;
    }

    public function setVille(string $Ville): static
    {
        $this->Ville = $Ville;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

 

    public function getEmployer(): ?self
    {
        return $this->employer;
    }

    public function setEmployer(?self $employer): static
    {
        $this->employer = $employer;

        return $this;
    }

    public function getIsVerified()
    {
        return $this->isVerified;
    }

    public function setIsVerified(Boolean $isVerified):self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(self $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setEmployer($this);
        }

        return $this;
    }

    public function removeClient(self $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getEmployer() === $this) {
                $client->setEmployer(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId(); // Convertit l'ID en chaîne de caractères
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getSent(): Collection
    {
        return $this->sent;
    }

    public function addSent(Messages $sent): static
    {
        if (!$this->sent->contains($sent)) {
            $this->sent->add($sent);
            $sent->setSender($this);
        }

        return $this;
    }

    public function removeSent(Messages $sent): static
    {
        if ($this->sent->removeElement($sent)) {
            // set the owning side to null (unless already changed)
            if ($sent->getSender() === $this) {
                $sent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Messages>
     */
    public function getReceived(): Collection
    {
        return $this->received;
    }

    public function addReceived(Messages $received): static
    {
        if (!$this->received->contains($received)) {
            $this->received->add($received);
            $received->setRecipient($this);
        }

        return $this;
    }

    public function removeReceived(Messages $received): static
    {
        if ($this->received->removeElement($received)) {
            // set the owning side to null (unless already changed)
            if ($received->getRecipient() === $this) {
                $received->setRecipient(null);
            }
        }

        return $this;
    }

      /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
{
    if (!$this->orders->contains($order)) {
        $this->orders[] = $order;
        $order->setUser($this);
    }

    return $this;
}

public function removeOrder(Order $order): self
{
    if ($this->orders->removeElement($order)) {
        // set the owning side to null (unless already changed)
        if ($order->getUser() === $this) {
            $order->setUser(null);
        }
    }

    return $this;
}
    
}

  



