<?php
namespace Cyberjaw\SpeedyCourierBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('speedy_courier');
        $root = $tree->getRootNode();

        $root
            ->children()
            ->scalarNode('base_uri')->defaultValue('%env(resolve:SPEEDY_BASE_URI)%')->end()
            ->scalarNode('username')->defaultValue('%env(SPEEDY_USER)%')->cannotBeEmpty()->end()
            ->scalarNode('password')->defaultValue('%env(SPEEDY_PASS)%')->cannotBeEmpty()->end()
            ->floatNode('timeout')->defaultValue(20.0)->min(1)->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
            ->end();

        return $tree;
    }
}
