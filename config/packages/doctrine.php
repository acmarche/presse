<?php

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine) {

    $emMda = $doctrine->orm()->entityManager('default');
    $emMda->mapping('AcMarchePresse')
        ->isBundle(false)
        ->type('attribute')
        ->dir('%kernel.project_dir%/src/AcMarche/Presse/src/Entity')
        ->prefix('AcMarche\Presse')
        ->alias('AcMarchePresse');
};
