<?php

namespace AcMarche\Presse\Entity;

use DateTimeInterface;
use DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;
use DateTimeImmutable;
use AcMarche\Presse\Doctrine\IdEntityTrait;
use AcMarche\Presse\Doctrine\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * ORM\Table(uniqueConstraints={
 *     ORM\UniqueConstraint(columns={"parent_id","date_album"})
 * })
 * @ORM\Entity(repositoryClass="AcMarche\Presse\Repository\AlbumRepository")
 * @UniqueEntity(fields={"parent","date_album"}, message="Un album a déjà cette date")
 * @Vich\Uploadable
 */
class Album
{
    use TimestampableEntityTrait;
    use IdEntityTrait;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private ?string $nom = null;

    /**
     * @ORM\Column(type="date")
     */
    private ?DateTimeInterface $date_album = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\ManyToOne(targetEntity="AcMarche\Presse\Entity\Album", inversedBy="albums")
     */
    private ?Album $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="AcMarche\Presse\Entity\Album", mappedBy="parent", cascade={"remove"})
     */
    private Collection $albums;

    /**
     * @ORM\OneToMany(targetEntity="AcMarche\Presse\Entity\Article", mappedBy="album", cascade={"remove"} ,orphanRemoval=true)
     */
    private Collection $articles;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="album_image", fileNameProperty="imageName", size="imageSize")
     */
    private ?File $image = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $imageName = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $imageSize = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $directoryName = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->albums = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
        if ($this->nom) {
            return $this->nom;
        }

        return $this->date_album->format('d-m-Y');
    }

    public function niceName(): string
    {

        if ($this->nom) {
            return $this->nom;
        }

        if ($this->getParent() === null) {
            return $this->date_album->format('F Y');
        }

        return $this->date_album->format('d-m-Y');

    }

    /**
     * @return string|null
     */
    public function getDirectoryName(): string
    {
        return $this->directoryName;
    }

    /**
     * @param string $directoryName
     */
    public function setDirectoryName(string $directoryName): void
    {
        $this->directoryName = $directoryName;
    }

    public function getFirstArticle(): ?Article
    {
        if (count($this->articles) > 0) {
            return $this->articles->first();
        }

        return null;
    }

    /**
     * If manually uploading a image (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|UploadedFile $imageFile
     * @throws Exception
     */
    public function setImage(?File $image = null): void
    {
        $this->image = $image;

        if (null !== $image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the image is lost
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function getImage(): ?File
    {
        return $this->image;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParent(): ?\AcMarche\Presse\Entity\Album
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Album[]
     */
    public function getAlbums(): ArrayCollection
    {
        return $this->albums;
    }

    public function addAlbum(Album $album): self
    {
        if (!$this->albums->contains($album)) {
            $this->albums[] = $album;
            $album->setParent($this);
        }

        return $this;
    }

    public function removeAlbum(Album $album): self
    {
        if ($this->albums->contains($album)) {
            $this->albums->removeElement($album);
            // set the owning side to null (unless already changed)
            if ($album->getParent() === $this) {
                $album->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): ArrayCollection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAlbum($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAlbum() === $this) {
                $article->setAlbum(null);
            }
        }

        return $this;
    }

    public function getDateAlbum(): DateTimeInterface
    {
        return $this->date_album;
    }

    public function setDateAlbum(DateTimeInterface $date_album): self
    {
        $this->date_album = $date_album;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }
}
