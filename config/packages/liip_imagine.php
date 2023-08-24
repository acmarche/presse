<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\LiipImagineConfig;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('liip_imagine', ['resolvers' => ['default' => ['web_path' => null]]]);

    $containerConfigurator->extension(
        'liip_imagine',
        [
            'filter_sets' => [
                'cache' => null,
                'miniature' => [
                    'quality' => 100,
                    'filters' => ['thumbnail' => ['size' => [240, 180], 'mode' => 'outbound']],
                ],
                'my_thumb' => [
                    'quality' => 100,
                    'filters' => ['thumbnail' => ['size' => [120, 45], 'mode' => 'inset']],
                ],
                'my_heighten_filter' => [
                    'quality' => 100,
                    'filters' => [
                        'relative_resize' => [
                            'heighten' => 120,
                        ],
                    ],
                ],
            ],
        ],
    );
};

return static function (LiipImagineConfig $liipImagineConfig): void {

    $liipImagineConfig
        ->driver('gd')
        ->resolvers('default', ['web_path' => []]);

    $liipImagineConfig->filterSet('miniature', [
        'jpeg_quality' => 100,
        'filters' => [
            'thumbnail' => [
                'size' => '240, 180',
                'mode' => 'outbound',
            ],
        ],
    ]);

    $liipImagineConfig->filterSet('my_thumb', [
        'jpeg_quality' => 100,
        'filters' => [
            'thumbnail' => [
                'size' => '120, 45',
                'mode' => 'inset',
            ],
        ],
    ]);

    $liipImagineConfig->filterSet('my_heighten_filter', [
        'filters' => [
            'relative_resize' => [
                'heighten' => 120,
            ],
        ],
    ]);
};
