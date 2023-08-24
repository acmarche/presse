<?php

declare(strict_types=1);

use AcMarche\Presse\Service\AlbumDirectoryNamer;
use AcMarche\Presse\Service\ArticleDirectoryNamer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'fr');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AcMarche\Presse\\', __DIR__.'/../src/*')
        ->exclude([__DIR__.'/../src/{Entity,Tests}']);

    $services->set(AlbumDirectoryNamer::class)
        ->public();

    $services->set(ArticleDirectoryNamer::class)
        ->public();
};
