<?php

/**
 * @license MIT
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Niji\XmlParserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class XmlParsersExtension.
 *
 * Manage dependencies injections.
 */
class XmlParserExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array                                                   $configs
     *   List of configs.
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *   The dependencies container.
     *
     * @throws \Exception
     *   Whenever an error occurs.
     *
     * @SuppressWarnings("unused")
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('parsers.yaml');
    }
}
