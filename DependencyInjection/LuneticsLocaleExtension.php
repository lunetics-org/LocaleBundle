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
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->bindParameters($container, $this->getAlias(), $config);

        $loader_yml = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader_yml->load('twig.yml');
        $loader_xml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader_xml->load('locale_detector_service.xml');
    }

    public function getAlias()
    {
        return 'lunetics_locale';
    }

    public function bindParameters(ContainerBuilder $container, $name, $config)
    {
        if(is_array($config) && empty($config[0]))
        {
            foreach ($config as $key => $value) 
            {
                $this->bindParameters($container, $name.'.'.$key, $value);
            }
        }
        else
            {
                $container->setParameter($name, $config);
            }
    }
}
