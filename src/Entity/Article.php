<?php

namespace AcMarche\Presse\Entity;

use AcMarche\Presse\Doctrine\IdEntityTrait;
use AcMarche\Presse\Repository\ArticleRepository;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Stringable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article implements TimestampableInterface, Stringable
{
    use IdEntityTrait;
    use TimestampableTrait;

    #[ORM\Column(type: 'string', length: 200)]
    public ?string $nom = null;
    #[ORM\Column(type: 'string', length: 80)]
    public ?string $mime = null;
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;
    #[ORM\Column(type: 'date', nullable: false)]
    public ?DateTimeInterface $dateArticle = null;

    #[Vich\UploadableField(mapping: 'article_file', fileNameProperty: 'fileName', size: 'fileSize')]
    public ?File $file = null;
    #[ORM\Column(type: 'string', length: 255)]
    public ?string $fileName = null;
    #[ORM\Column(type: 'integer')]
    public ?int $fileSize = null;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)] private ?Album $album;

    public function __construct(?Album $album)
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->album = $album;
    }

    public function __toString(): string
    {
        return (string)$this->nom;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $imageFile
     *
     * @throws Exception
     */
    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }
}
