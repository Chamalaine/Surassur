<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ListingRepository")
 */
class Listing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateEnvoi;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Assure", inversedBy="listings")
     */
    private $assures;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Souscripteur", inversedBy="listings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $souscripteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Intermediaire", inversedBy="listings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $intermediaire;

    public function __construct()
    {
        $this->assures = new ArrayCollection();
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(?\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * @return Collection|Assure[]
     */
    public function getassures(): Collection
    {
        return $this->assures;
    }

    public function addassures(Assure $assures): self
    {
        if (!$this->assures->contains($assures)) {
            $this->assures[] = $assures;
        }

        return $this;
    }

    public function removeassures(Assure $assures): self
    {
        if ($this->assures->contains($assures)) {
            $this->assures->removeElement($assures);
        }

        return $this;
    }

    public function getSouscripteur(): ?Souscripteur
    {
        return $this->souscripteur;
    }

    public function setSouscripteur(?Souscripteur $souscripteur): self
    {
        $this->souscripteur = $souscripteur;

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
}
