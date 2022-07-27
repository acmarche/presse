<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'framework',
        [
            'default_locale' => 'fr',
            'session' => [
                'handler_id' => 'session.handler.native_file',
                'save_path' => '%kernel.cache_dir%/../../sessions',
                'cookie_secure' => 'auto',
                'cookie_samesite' => 'lax',
                'cookie_lifetime' => 0,
            ],
        ]
    );
};
