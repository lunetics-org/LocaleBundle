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
                ->scalarNode('strict_mode')
                    ->defaultFalse()
                ->end()
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
                ->arrayNode('cookie_guesser')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('session_guesser')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('Lunetics\LocaleBundle\LocaleGuesser\SessionLocaleGuesser')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query_guesser')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')
                            ->defaultValue('Lunetics\LocaleBundle\LocaleGuesser\QueryLocaleGuesser')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cookie')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('set_on_detection')->defaultFalse()->end()
                        ->scalarNode('set_on_switch')->defaultTrue()->end()
                        ->scalarNode('class')->defaultValue('Lunetics\LocaleBundle\Cookie\LocaleCookie')->end()
                        ->scalarNode('name')->defaultValue('lunetics_locale')->end()
                        ->scalarNode('ttl')->defaultValue('86400')->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultValue(null)->end()
                        ->scalarNode('secure')->defaultFalse()->end()
                        ->scalarNode('httpOnly')->defaultTrue()->end()
                     ->end()
                ->end()
                ->arrayNode('session')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('variable')->defaultValue('lunetics_locale')->end()
                     ->end()
                ->end()
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('parameter_name')->defaultValue('_locale')->end()
                     ->end()
                ->end()
                ->arrayNode('switcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('links')->end()
                        ->scalarNode('show_current_locale')->defaultFalse()->end()
                        ->scalarNode('use_controller')->defaultTrue()->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
