<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'liip_imagine',
        [
            'driver' => 'gd',
            'resolvers' => ['default' => ['web_path' => null]],
            'filter_sets' => [
                'cache' => null,
                'my_thumb' => [
                    'jpeg_quality' => 100,
                    'filters' => ['thumbnail' => ['size' => [120, 45], 'mode' => 'inset']],
                ],
                'miniature' => [
                    'jpeg_quality' => 100,
                    'filters' => ['thumbnail' => ['size' => [240, 180], 'mode' => 'outbound']],
                ],
                'my_heighten_filter' => ['filters' => ['relative_resize' => ['heighten' => 120]]],
            ],
        ]
    );
};
