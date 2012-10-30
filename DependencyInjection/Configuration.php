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

        $validStatuscodes = array(300, 301, 302, 303, 307);

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
                        ->scalarNode('set_on_change')->defaultTrue()->end()
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
                ->arrayNode('form')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('languages_only')->defaultTrue()->end()
                        ->booleanNode('strict_mode')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('switcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('links')->end()
                        ->booleanNode('show_current_locale')->defaultFalse()->end()
                        ->scalarNode('redirect_to_route')->defaultNull()->end()
                        ->scalarNode('redirect_statuscode')->defaultValue('302')->end()
                        ->booleanNode('use_controller')->defaultFalse()->end()
                        ->booleanNode('use_referrer')->defaultTrue()->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return is_null($v['redirect_to_route']);})
                            ->thenInvalid('You need to specify a default fallback route for the use_controller configuration')
                        ->ifTrue(function($v) use ($validStatuscodes) { return !in_array(intval($v['redirect_statuscode']), $validStatuscodes);})
                            ->thenInvalid(sprintf("Invalid HTTP statuscode. Available statuscodes for redirection are:\n\n%s \n\nSee reference for HTTP status codes", implode(", ",$validStatuscodes)))
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
