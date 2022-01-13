<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 11/01/19
 * Time: 11:38.
 */

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Album;
use Symfony\Component\Filesystem\Filesystem;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

class AlbumService
{
    public function __construct(
        private PropertyMappingFactory $propertyMappingFactory,
        private Filesystem $storage
    ) {
    }

    /**
     * @return Album[]
     *
     * donne vetement enfant
     * premier parent => mode : indice 0
     */
    public function getPath(Album $album): array
    {
        $path = $this->getFullPath($album);
        $path[] = $album;

        return $path;
    }

    public function getFullPath(Album $album): array
    {
        $path = [];
        $parent = $album->getParent();
        if (null !== $parent) {
            $path[] = $parent;
            $path = array_merge(self::getFullPath($parent), $path);
        }

        return $path;
    }

    public static function getDirectory(Album $album): string
    {
        $parent = $album->getParent();
        $paths = [];
        if (null !== $parent) {
            $paths[] = $parent->getDateAlbum()->format('Y-m-d');
        }

        $paths[] = $album->getDateAlbum()->format('Y-m-d');

        return implode(\DIRECTORY_SEPARATOR, $paths);
    }

    public function createFolder(Album $album)
    {
        $mappings = $this->propertyMappingFactory->fromObject($album);
        $mapping = $mappings[0];
        $path = $mapping->getUploadDestination();
        $directory = $path.\DIRECTORY_SEPARATOR.self::getDirectory($album);
        $this->storage->mkdir($directory);
    }
}
