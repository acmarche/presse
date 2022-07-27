<?php

use Symfony\Config\VichUploaderConfig;

return static function (VichUploaderConfig $vichUploaderConfig): void {
    $vichUploaderConfig
        ->dbDriver('orm')
        ->mappings(
            'article_file',
            [
                'uri_prefix' => '/files',
                'upload_destination' => '%kernel.project_dir%/public/files',
                'namer' => ['service' => 'Vich\UploaderBundle\Naming\OrignameNamer'],
                'directory_namer' => 'AcMarche\Presse\Service\ArticleDirectoryNamer',
            ]
        );
    $vichUploaderConfig->mappings('album_image', [
        'uri_prefix' => '/albums',
        'upload_destination' => '%kernel.project_dir%/public/files',
        'directory_namer' => 'AcMarche\Presse\Service\AlbumDirectoryNamer',
    ]);
};
