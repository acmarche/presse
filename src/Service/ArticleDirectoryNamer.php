<?php
/**
 * This file is part of presse application
 * @author jfsenechal <jfsenechal@gmail.com>
 * @date 16/10/19
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace AcMarche\Presse\Service;

use AcMarche\Presse\Entity\Article;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

class ArticleDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * Creates a directory name for the file being uploaded.
     *
     * @param Article $object The object the upload is attached to
     * @param PropertyMapping $mapping The mapping to use to manipulate the given object
     *
     * @return string The directory name
     */
    public function directoryName($object, PropertyMapping $mapping): string
    {
        $album = $object->getAlbum();
        if ($album === null) {
            return 'lost';
        }

        return $album->getDirectoryName();
    }
}