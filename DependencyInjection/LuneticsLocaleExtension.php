<?php

namespace Lunetics\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LuneticsLocaleExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader_yml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader_yml->load('twig.yml');
        $loader_xml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader_xml->load('locale_detector_service.xml');


        /**
        * @TODO: Remap config parameters
        */

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lunetics_locale.switch.redirect_route', isset($config['switch_router']['redirect_to_route']) ? $config['switch_router']['redirect_to_route'] : null);
        $container->setParameter('lunetics_locale.switch.redirect_url', $config['switch_router']['redirect_to_url']);
        $container->setParameter('lunetics_locale.switch.use_referrer', $config['switch_router']['use_referrer']);
        $container->setParameter('lunetics_locale.allowed_languages', $config['allowed_languages']);
        $container->setParameter('lunetics_locale.change_language.show_foreign_languagenames', $config['change_language']['show_foreign_languagenames']);
        $container->setParameter('lunetics_locale.change_language.show_first_uppercase', $config['change_language']['show_first_uppercase']);
        $container->setParameter('lunetics_locale.change_language.show_languagetitle', $config['change_language']['show_languagetitle']);
        $container->setParameter('lunetics_locale.detection.priority', $config['detection']['priority']);
        $container->setParameter('lunetics_locale.detection.browser_detector_class', $config['detection']['browser_detector_class']);
        $container->setParameter('lunetics_locale.detection.router_detector_class', $config['detection']['router_detector_class']);
        $container->setParameter('lunetics_locale.detection.cookie_detector_class', $config['detection']['cookie_detector_class']);
        $container->setParameter('lunetics_locale.detection.custom_detector_class', $config['detection']['custom_detector_class']);

    }
}
