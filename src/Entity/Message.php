<?php

namespace AcMarche\Presse\Entity;

use AcMarche\Presse\Doctrine\IdEntityTrait;
use AcMarche\Presse\Repository\MessageRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[Vich\Uploadable]
class Message implements TimestampableInterface,Stringable
{
    use TimestampableTrait;
    use IdEntityTrait;

    #[ORM\Column(type: 'text', nullable: false)]
    public string $subject;
    #[ORM\Column(type: 'text')]
    public ?string $text = null;
    #[ORM\Column()]
    public bool $sended = false;
    #[ORM\Column(length: 50, nullable: true)]
    public ?string $sender = null;

    #[Vich\UploadableField(mapping: 'message_attachment', fileNameProperty: 'fileName', size: 'fileSize')]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    public ?string $fileName = null;

    #[ORM\Column(nullable: true)]
    public ?int $fileSize = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function __toString(): string
    {
        return " ".$this->id;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $file
     */
    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

}
