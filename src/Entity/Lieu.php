<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomLieu = null;

    #[ORM\Column(length: 255)]
    private ?string $rue = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(length: 255)]
    private ?string $nomVille = null;

    #[ORM\Column(length: 255)]
    private ?string $codePostal = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'lieu', orphanRemoval: true)]
    private Collection $ListeSorties;

    public function __construct()
    {
        $this->ListeSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomLieu(): ?string
    {
        return $this->nomLieu;
    }

    public function setNomLieu(string $nomLieu): static
    {
        $this->nomLieu = $nomLieu;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): static
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getNomVille(): ?string
    {
        return $this->nomVille;
    }

    public function setNomVille(string $nomVille): static
    {
        $this->nomVille = $nomVille;

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

    /**
     * @return Collection<int, Sortie>
     */
    public function getListeSorties(): Collection
    {
        return $this->ListeSorties;
    }

    public function addListeSorty(Sortie $listeSorty): static
    {
        if (!$this->ListeSorties->contains($listeSorty)) {
            $this->ListeSorties->add($listeSorty);
            $listeSorty->setLieu($this);
        }

        return $this;
    }

    public function removeListeSorty(Sortie $listeSorty): static
    {
        if ($this->ListeSorties->removeElement($listeSorty)) {
            // set the owning side to null (unless already changed)
            if ($listeSorty->getLieu() === $this) {
                $listeSorty->setLieu(null);
            }
        }

        return $this;
    }
}
