<?php

namespace AcMarche\Presse\Entity;

use AcMarche\Presse\Repository\ArticleRepository;
use Stringable;
use DateTimeInterface;
use DateTime;
use Exception;
use DateTimeImmutable;
use AcMarche\Presse\Doctrine\IdEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article implements TimestampableInterface, Stringable
{
    use IdEntityTrait;
    use TimestampableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;
    #[ORM\Column(type: 'string', length: 200)]
    private ?string $nom = null;
    #[ORM\Column(type: 'string', length: 80)]
    private ?string $mime = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(type: 'date', nullable: false)]
    private ?DateTimeInterface $dateArticle = null;
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="article_file", fileNameProperty="fileName", size="fileSize")
     */
    private ?File $file = null;
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $fileName = null;
    #[ORM\Column(type: 'integer')]
    private ?int $fileSize = null;
    public function __construct(#[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'articles')] #[ORM\JoinColumn(nullable: false)] private ?Album $album)
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }
    public function __toString(): string
    {
        return (string) $this->nom;
    }
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $imageFile
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
    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
    public function getMime(): ?string
    {
        return $this->mime;
    }
    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
    public function getDateArticle(): ?\DateTimeInterface
    {
        return $this->dateArticle;
    }
    public function setDateArticle(DateTimeInterface $dateArticle): self
    {
        $this->dateArticle = $dateArticle;

        return $this;
    }
    public function getFileName(): ?string
    {
        return $this->fileName;
    }
    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }
    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
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
