<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 11/01/19
 * Time: 11:38
 */

namespace AcMarche\Presse\Service;


use AcMarche\Presse\Entity\Album;
use AcMarche\Presse\Repository\AlbumRepository;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
use Vich\UploaderBundle\Storage\StorageInterface;

class AlbumService
{

    private AlbumRepository $albumRepository;
    private PropertyMappingFactory $propertyMappingFactory;
    private Filesystem $storage;


    public function __construct(
        AlbumRepository $albumRepository,
        PropertyMappingFactory $propertyMappingFactory,
        Filesystem $storage
    ) {
        $this->albumRepository = $albumRepository;
        $this->propertyMappingFactory = $propertyMappingFactory;
        $this->storage = $storage;
    }

    /**
     * @param Album $album
     * @return Album[]
     *
     * donne vetement enfant
     * premier parent => mode : indice 0
     *
     */
    function getPath(Album $album): array
    {
        $path = $this->getFullPath($album);
        $path[] = $album;

        return $path;
    }

    function getFullPath(Album $album): array
    {
        $path = [];
        $parent = $album->getParent();
        if ($parent !== null) {
            $path[] = $parent;
            $path = array_merge(self::getFullPath($parent), $path);
        }

        return $path;
    }

    static function getDirectory(Album $album): string
    {
        $parent = $album->getParent();
        $paths = [];
        if ($parent !== null) {
            $paths[] = $parent->getDateAlbum()->format('Y-m-d');
        }

        $paths[] = $album->getDateAlbum()->format('Y-m-d');

        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    /**
     * @param Album $album
     * @throws IOException On any directory creation failure
     */
    public function createFolder(Album $album): void
    {
        $mappings = $this->propertyMappingFactory->fromObject($album);
        $mapping = $mappings[0];
        $path = $mapping->getUploadDestination();
        $directory = $path.DIRECTORY_SEPARATOR.self::getDirectory($album);
        $this->storage->mkdir($directory);
    }

}