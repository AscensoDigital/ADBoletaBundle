<?php

namespace AscensoDigital\BoletaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        /*$rootNode
            ->children()
                ->arrayNode('database')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('driver')->defaultValue('pdo_pgsql')->cannotBeEmpty()->end()
                        ->scalarNode('host')->defaultValue('localhost')->cannotBeEmpty()->end()
                        ->scalarNode('port')->defaultValue('5432')->cannotBeEmpty()->end()
                        ->scalarNode('name')->defaultValue('ad_boletadb')->cannotBeEmpty()->end()
                        ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ->end();*/

        /*$this->addFiltroSection($rootNode);*/

        return $treeBuilder;
    }

    private function addFiltroSection(ArrayNodeDefinition $rootNode) {
        $rootNode
            ->fixXmlConfig('filtro')
            ->children()
                ->arrayNode('filtros')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('type')
                            ->defaultValue('Symfony\Bridge\Doctrine\Form\Type\EntityType')
                            ->cannotBeEmpty()
                            ->info('Clase del tipo de form que será el filtro.')
                            ->example('Symfony\Bridge\Doctrine\Form\Type\EntityType (see http://symfony.com/doc/current/reference/forms/types.html)')
                        ->end()
                        ->variableNode('table_alias')
                            ->cannotBeEmpty()
                            ->info('String o Array con Alias de la(s) "entity" usada(s) para filtrar')
                            ->example('eg: para entity "Pais" alias "p"')
                        ->end()
                        ->scalarNode('field')
                            ->defaultValue('id')
                            ->cannotBeEmpty()
                            ->info('Nombre del "field" que se filtra de la tabla con "table_alias"')
                            ->example('eg: id')
                        ->end()
                        ->scalarNode('operator')
                            ->defaultValue('in')
                            ->cannotBeEmpty()
                            ->info('Valor para el operador de comparación')
                            ->example('eg para equal: eq (see http://www.doctrine-project.org/api/orm/2.5/class-Doctrine.ORM.Query.Expr.html)')
                        ->end()
                        ->scalarNode('function')
                            ->info('Funcion SQL a aplicar')
                            ->example('eg: date')
                        ->end()
                        ->scalarNode('query_builder_method')
                            ->info('Nombre del metodo usado para generar la opción query_builder')
                            ->example('eg: getQueryBuilderFindAll')
                        ->end()
                        ->booleanNode('query_builder_perfil')
                            ->defaultFalse()
                            ->info('Determina si al query_builder se le pasa como parámetro el objeto perfil')
                        ->end()
                        ->booleanNode('query_builder_user')
                            ->defaultFalse()
                            ->info('Determina si al query_builder se le pasa como parámetro el objeto user')
                        ->end()
                        ->arrayNode('options')
                            ->info('Lista de opciones según "type" del filtro.')
                            ->prototype('variable')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
