<?php

use Symfony\Config\VichUploaderConfig;
use Vich\UploaderBundle\Naming\UniqidNamer;

return static function (VichUploaderConfig $vichUploaderConfig): void {
    $vichUploaderConfig
        ->dbDriver('orm')
        ->mappings(
            'article_file',
            [
                'uri_prefix' => '/files',
                'upload_destination' => '%kernel.project_dir%/public/files',
                'namer' => ['service' => UniqidNamer::class],
                'directory_namer' => AcMarche\Presse\Service\ArticleDirectoryNamer::class,
            ],
        );
    $vichUploaderConfig->mappings('album_image', [
        'uri_prefix' => '/albums',
        'upload_destination' => '%kernel.project_dir%/public/files',
        'directory_namer' => AcMarche\Presse\Service\AlbumDirectoryNamer::class,
    ]);
    $vichUploaderConfig->mappings('message_attachment', [
        'uri_prefix' => '/messages',
        'upload_destination' => '%kernel.project_dir%/public/files/messages',
        'namer' => Vich\UploaderBundle\Naming\SmartUniqueNamer::class,
    ]);
};
