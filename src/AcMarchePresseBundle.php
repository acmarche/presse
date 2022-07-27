<?php

namespace AcMarche\Presse;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcMarchePresseBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/packages/doctrine.php');
        $container->import('../config/packages/framework.php');
        $container->import('../config/packages/liip_imagine.php');
        $container->import('../config/packages/security.php');
        $container->import('../config/packages/twig.php');
        $container->import('../config/packages/vich_uploader.php');
    }
}
