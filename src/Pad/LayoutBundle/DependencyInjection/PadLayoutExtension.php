<?php

namespace Pad\LayoutBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\Resource\FileResource;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PadLayoutExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load( array $config, ContainerBuilder $container )
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator( __DIR__ . '/../Resources/config' )
        );

        // Base services override
        $loader->load( 'services.yml' );
        // Default settings
        $loader->load( 'default_settings.yml' );
    }

    /**
     * Loads DemoBundle configuration.
     *
     * @param ContainerBuilder $container
     */
    public function prepend( ContainerBuilder $container )
    {
        $ezpageConfigFile = __DIR__ . '/../Resources/config/system.yml';
        $ezpageConfig = Yaml::parse( file_get_contents( $ezpageConfigFile ) );
        $container->prependExtensionConfig( 'ezpublish', $ezpageConfig );
        $container->addResource( new FileResource( $ezpageConfigFile ) );

        $ezpageConfigFile = __DIR__ . '/../Resources/config/siteaccess.yml';
        $ezpageConfig = Yaml::parse( file_get_contents( $ezpageConfigFile ) );
        $container->prependExtensionConfig( 'ezpublish', $ezpageConfig );
        $container->addResource( new FileResource( $ezpageConfigFile ) );

        $ezpageConfigFile = __DIR__ . '/../Resources/config/image_variations.yml';
        $ezpageConfig = Yaml::parse( file_get_contents( $ezpageConfigFile ) );
        $container->prependExtensionConfig( 'ezpublish', $ezpageConfig );
        $container->addResource( new FileResource( $ezpageConfigFile ) );

    }
}
