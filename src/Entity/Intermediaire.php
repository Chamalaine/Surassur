<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Captcha\Bundle\CaptchaBundle\Validator\Constraints as CaptchaAssert;


/**
 * @ORM\Entity
 * @UniqueEntity("email")
 */


/**
 * @ORM\Entity(repositoryClass="App\Repository\IntermediaireRepository")
 */
class Intermediaire implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=90)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=900)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $siret;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Listing", mappedBy="intermediaire")
     */
    private $listings;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Souscripteur", inversedBy="intermediaires")
     */
    private $souscripteurs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Assure", mappedBy="intermediaire")
     */
    private $assures;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $cp;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $ville;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DateToken;




 
    public function __construct()
    {
        $this->listings = new ArrayCollection();
        $this->souscripteurs = new ArrayCollection();
        $this->assures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

       /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return Collection|Listing[]
     */
    public function getListings(): Collection
    {
        return $this->listings;
    }

    public function addListing(Listing $listing): self
    {
        if (!$this->listings->contains($listing)) {
            $this->listings[] = $listing;
            $listing->setIntermediaire($this);
        }

        return $this;
    }

    public function removeListing(Listing $listing): self
    {
        if ($this->listings->contains($listing)) {
            $this->listings->removeElement($listing);
            // set the owning side to null (unless already changed)
            if ($listing->getIntermediaire() === $this) {
                $listing->setIntermediaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Souscripteur[]
     */
    public function getSouscripteurs(): Collection
    {
        return $this->souscripteurs;
    }

    public function addSouscripteur(Souscripteur $souscripteur): self
    {
        if (!$this->souscripteurs->contains($souscripteur)) {
            $this->souscripteurs[] = $souscripteur;
        }

        return $this;
    }

    public function removeSouscripteur(Souscripteur $souscripteur): self
    {
        if ($this->souscripteurs->contains($souscripteur)) {
            $this->souscripteurs->removeElement($souscripteur);
        }

        return $this;
    }

    /**
     * @return Collection|Assure[]
     */
    public function getAssures(): Collection
    {
        return $this->assures;
    }

    public function addAssure(Assure $assure): self
    {
        if (!$this->assures->contains($assure)) {
            $this->assures[] = $assure;
            $assure->setIntermediaire($this);
        }

        return $this;
    }

    public function removeAssure(Assure $assure): self
    {
        if ($this->assures->contains($assure)) {
            $this->assures->removeElement($assure);
            // set the owning side to null (unless already changed)
            if ($assure->getIntermediaire() === $this) {
                $assure->setIntermediaire(null);
            }
        }

        return $this;
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

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return string
     */
    public function getResetToken(): string
    {
        return $this->resetToken;
    }

    /**
     * @param string $resetToken
     */
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getDateToken(): ?\DateTimeInterface
    {
        return $this->DateToken;
    }

    public function setDateToken(?\DateTimeInterface $DateToken): self
    {
        $this->DateToken = $DateToken;

        return $this;
    }




}
