<?php

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $frameworkConfig->router()->defaultUri('%env(PRESSE_URI)%');
    $frameworkConfig->defaultLocale('fr');
    $frameworkConfig->session([
        'handler_id' => 'session.handler.native_file',
        'save_path' => '%kernel.cache_dir%/../../sessions',
    ]);
};
