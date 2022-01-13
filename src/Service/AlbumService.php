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
use Symfony\Component\Filesystem\Filesystem;

class AlbumService
{

    public function __construct(private AlbumRepository $albumRepository, private Filesystem $storage)
    {
    }

    /**
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
}