<?php

namespace AscensoDigital\BoletaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ad_boleta');

        $rootNode
            ->children()
                ->scalarNode('ruta_boletas')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('boleta_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('menu_superior_slug')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('menu_superior_load_class')->isRequired()->cannotBeEmpty()->end()
                /*->arrayNode('database')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('driver')->defaultValue('pdo_pgsql')->cannotBeEmpty()->end()
                        ->scalarNode('host')->defaultValue('localhost')->cannotBeEmpty()->end()
                        ->scalarNode('port')->defaultValue('5432')->cannotBeEmpty()->end()
                        ->scalarNode('name')->defaultValue('ad_boletadb')->cannotBeEmpty()->end()
                        ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()*/
            ->end()
        ->end();

        return $treeBuilder;
    }
}
