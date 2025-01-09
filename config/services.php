<?php

declare(strict_types=1);

use AcMarche\Presse\Security\LdapPresse;
use AcMarche\Presse\Service\AlbumDirectoryNamer;
use AcMarche\Presse\Service\ArticleDirectoryNamer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'fr');

    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->load('AcMarche\Presse\\', __DIR__.'/../src/*')
        ->exclude([__DIR__.'/../src/{Entity,Tests}']);

    if (interface_exists(LdapInterface::class)) {
        $services
            ->set(Ldap::class)
            ->args(['@Symfony\Component\Ldap\Adapter\ExtLdap\Adapter'])
            ->tag('ldap');
        $services->set(Adapter::class)->args(
            [
                [
                    'host' => '%env(ACLDAP_URL)%',
                    'port' => 636,
                    'encryption' => 'ssl',
                    'options' => [
                        'protocol_version' => 3,
                        'referrals' => false,
                    ],
                ],
            ],
        );

        $services
            ->set(LdapPresse::class)
            ->arg('$adapter', service(Adapter::class))
            ->tag('ldap'); // necessary for new LdapBadge(LdapPresse::class)
    }

    $services
        ->set(AlbumDirectoryNamer::class)
        ->public();

    $services
        ->set(ArticleDirectoryNamer::class)
        ->public();
};
