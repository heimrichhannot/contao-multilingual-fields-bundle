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
                ->scalarNode('fallback_language')->defaultValue('de')->end()
                ->arrayNode('languages')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('data_containers')
                    ->useAttributeAsKey('table')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('fields')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->cannotBeEmpty()->end()
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
