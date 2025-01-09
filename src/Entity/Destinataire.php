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
    public ?string $nom = null;
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    public ?string $prenom = null;
    #[ORM\Column(type: 'string', length: 100)]
    public ?string $email = null;
    #[ORM\Column(type: 'string', length: 100)]
    public ?string $username = null;    
    #[ORM\Column(type: 'boolean')]
    public bool $attachment = false;
    #[ORM\Column(type: 'boolean')]
    public bool $notification = true;

    public function __toString(): string
    {
        return $this->nom.' '.$this->prenom;
    }
}
