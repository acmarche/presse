<?php

namespace AcMarche\Presse\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AcMarchePresseExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container): void
    {
        // get all bundles
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            foreach (array_keys($container->getExtensions()) as $name) {
                switch ($name) {
                    case 'doctrine':
                        $this->loadConfig($container, 'doctrine');
                        break;
                    case 'twig':
                        $this->loadConfig($container, 'twig');
                        break;
                    case 'liip_imagine':
                        $this->loadConfig($container, 'liip_imagine');
                        break;
                    case 'framework':
                        $this->loadConfig($container, 'framework');
                        $this->loadConfig($container, 'security');
                        break;
                    case 'vich_uploader':
                        $this->loadConfig($container, 'vich_uploader');
                        break;
                }
            }
        }
    }

    protected function loadConfig(ContainerBuilder $container, string $name): void
    {
        $configs = $this->loadYamlFile($container);

        $configs->load($name.'.yaml');
    }

    protected function loadYamlFile(ContainerBuilder $container): YamlFileLoader
    {
        return new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config/packages/')
        );
    }
}
