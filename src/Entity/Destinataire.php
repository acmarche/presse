<?php

namespace AcMarche\Presse\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AcMarche\Presse\Repository\DestinataireRepository")
 */
class Destinataire
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $nom = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $prenom = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exterieur;

    public function __toString()
    {
        return $this->nom.' '.$this->prenom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
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

    public function getEmail(): string
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
