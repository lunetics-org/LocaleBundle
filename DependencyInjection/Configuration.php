<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('lunetics_locale');

        $rootNode
            ->children()
                ->arrayNode('allowed_locales')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('guessing_order')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                ->prototype('scalar')->end()
                ->end()
                ->arrayNode('router_guesser')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser')
                        ->end()
                        ->booleanNode('check_query')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('browser_guesser')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
