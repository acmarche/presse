<?php

use AcMarche\Presse\Entity\User;
use AcMarche\Presse\Security\AppPresseAuthenticator;
use AcMarche\Presse\Security\PresseLdapAuthenticator;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\LdapInterface;

use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security) {
    $security->provider('presse_user_provider', [
        'entity' => [
            'class' => User::class,
            'property' => 'username',
        ],
    ]);

    $authenticators = [AppPresseAuthenticator::class];
    if (interface_exists(LdapInterface::class)) {
        $authenticators[] = PresseLdapAuthenticator::class;
        $main['form_login_ldap'] = [
            'service' => Ldap::class,
            'check_path' => 'app_login',
        ];
    }

    // @see Symfony\Config\Security\FirewallConfig
    $main = [
        'provider' => 'presse_user_provider',
        'logout' => [
            'path' => 'app_logout',
        ],
        'form_login' => [],
        'entry_point' => AppPresseAuthenticator::class,
        'custom_authenticators' => $authenticators,
        'login_throttling' => [
            'max_attempts' => 6, // per minute...
        ],
        'remember_me' => [
            'secret' => '%kernel.secret%',
            'lifetime' => 604800,
            'path' => '/',
            'always_remember_me' => true,
        ],
    ];

    $security
        ->firewall('main', $main)
        ->switchUser();

    $devFirewall = $security->firewall('dev');
    $devFirewall
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);
};