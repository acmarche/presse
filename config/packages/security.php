<?php

use AcMarche\Presse\Entity\User;
use AcMarche\Presse\Security\AppPresseAuthenticator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'security',
        [
            'password_hashers' => [User::class => ['algorithm' => 'auto']],
            'providers' => [
                'presse_user_provider' => [
                    'entity' => [
                        'class' => User::class,
                        'property' => 'username',
                    ],
                ],
            ],
            'firewalls' => [
                'main' => [
                    'custom_authenticator' => AppPresseAuthenticator::class,
                    'provider' => 'presse_user_provider',
                    'logout' => ['path' => 'app_logout'],
                ],
            ],
        ]
    );
};
