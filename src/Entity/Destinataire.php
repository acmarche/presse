<?php

namespace AcMarche\Presse\Entity;

use AcMarche\Presse\Doctrine\IdEntityTrait;
use AcMarche\Presse\Repository\DestinataireRepository;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: DestinataireRepository::class)]
class Destinataire implements Stringable
{
    use IdEntityTrait;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nom = null;
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $prenom = null;
    #[ORM\Column(type: 'string', length: 100)]
    private ?string $email = null;
    #[ORM\Column(type: 'boolean')]
    private $exterieur;

    public function __toString(): string
    {
        return $this->nom.' '.$this->prenom;
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

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getExterieur(): ?bool
    {
        return $this->exterieur;
    }

    public function setExterieur(bool $exterieur): self
    {
        $this->exterieur = $exterieur;

        return $this;
    }
}
