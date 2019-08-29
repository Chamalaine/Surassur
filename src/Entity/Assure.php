<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AssureRepository")
 */
class Assure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $prenom;

    /**
     * @ORM\Column(type="date")
     */
    private $dateNaissance;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=9)
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $cp;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $complement;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beneficiaire", mappedBy="assure", orphanRemoval=true)
     */
    private $beneficiaires;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Listing", mappedBy="assures")
     */
    private $listings;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Intermediaire", inversedBy="assures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $intermediaire;

    public function __construct()
    {
        $this->beneficiaires = new ArrayCollection();
        $this->listings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
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

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

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

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): self
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @return Collection|Beneficiaire[]
     */
    public function getBeneficier(): Collection
    {
        return $this->beneficiaires;
    }

    public function addBeneficier(Beneficiaire $beneficiaires): self
    {
        if (!$this->beneficiaires->contains($beneficiaires)) {
            $this->beneficiaires[] = $beneficiaires;
            $beneficiaires->setAssure($this);
        }

        return $this;
    }

    public function removeBeneficier(Beneficiaire $beneficiaires): self
    {
        if ($this->beneficiaires->contains($beneficiaires)) {
            $this->beneficiaires->removeElement($beneficiaires);
            // set the owning side to null (unless already changed)
            if ($beneficiaires->getAssure() === $this) {
                $beneficiaires->setAssure(null);
            }
        }

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
            $listing->addassures($this);
        }

        return $this;
    }

    public function removeListing(Listing $listing): self
    {
        if ($this->listings->contains($listing)) {
            $this->listings->removeElement($listing);
            $listing->removeassures($this);
        }

        return $this;
    }

    public function getIntermediaire(): ?Intermediaire
    {
        return $this->intermediaire;
    }

    public function setIntermediaire(?Intermediaire $intermediaire): self
    {
        $this->intermediaire = $intermediaire;

        return $this;
    }

    /**
     * @return Collection|Beneficiaire[]
     */
    public function getBeneficiaires(): Collection
    {
        return $this->beneficiaires;
    }

    public function addBeneficiaire(Beneficiaire $beneficiaire): self
    {
        if (!$this->beneficiaires->contains($beneficiaire)) {
            $this->beneficiaires[] = $beneficiaire;
            $beneficiaire->setAssure($this);
        }

        return $this;
    }

    public function removeBeneficiaire(Beneficiaire $beneficiaire): self
    {
        if ($this->beneficiaires->contains($beneficiaire)) {
            $this->beneficiaires->removeElement($beneficiaire);
            // set the owning side to null (unless already changed)
            if ($beneficiaire->getAssure() === $this) {
                $beneficiaire->setAssure(null);
            }
        }
        

        return $this;
    }
}
