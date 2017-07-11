<?php

namespace Fervo\EnumBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fervo_enum');

        $rootNode
            ->children()
                ->arrayNode('serialization')
                    ->children()
                        ->booleanNode('values_in_validation_message')->defaultFalse()->end()
                        ->arrayNode('translation')
                            ->children()
                                ->booleanNode('in_resource')->defaultFalse()->end()
                                ->booleanNode('in_validation_message')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('enums')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('doctrine_type')->end()
                        ->scalarNode('form_type')->end()
                    ->end()
                ->end()
            ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
