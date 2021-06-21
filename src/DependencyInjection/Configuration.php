<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const ROOT_ID = 'huh_multilingual_fields';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(static::ROOT_ID);

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('fallback_language')->isRequired()->defaultValue('de')->end()
                ->arrayNode('languages')->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('content_language_select')
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->arrayNode('types')->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('data_containers')->defaultValue([])
                    ->useAttributeAsKey('table')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('sql_condition')->end()
                            ->arrayNode('sql_condition_values')->defaultValue([])
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('fields')->defaultValue([])
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->isRequired()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
