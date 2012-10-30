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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Parser as YamlParser;

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

        // Fallback for missing intl extension
        $intlExtensionInstalled = extension_loaded('intl');
        $container->setParameter('lunetics_locale.intl_extension_installed', $intlExtensionInstalled);
        $iso3166 = array();
        $iso639one = array();
        $iso639two = array();
        $localeScript = array();
        if (!$intlExtensionInstalled) {
            $yamlParser = new YamlParser();
            $file = new FileLocator(__DIR__ . '/../Resources/config');
            $iso3166 = $yamlParser->parse(file_get_contents($file->locate('iso3166-1-alpha-2.yml')));
            $iso639one = $yamlParser->parse(file_get_contents($file->locate('iso639-1.yml')));
            $iso639two = $yamlParser->parse(file_get_contents($file->locate('iso639-2.yml')));
            $localeScript = $yamlParser->parse(file_get_contents($file->locate('locale_script.yml')));
        }
        $container->setParameter('lunetics_locale.intl_extension_fallback.iso3166', $iso3166);
        $mergedValues = array_merge($iso639one, $iso639two);
        $container->setParameter('lunetics_locale.intl_extension_fallback.iso639', $mergedValues);
        $container->setParameter('lunetics_locale.intl_extension_fallback.script', $localeScript);


        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('validator.xml');
        $loader->load('guessers.xml');
        $loader->load('services.xml');
        $loader->load('switcher.xml');
        $loader->load('form.xml');
    }
    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'lunetics_locale';
    }

    /**
     * Binds the params from config
     *
     * @param ContainerBuilder $container Containerbuilder
     * @param string           $name      Alias name
     * @param array            $config    Configuration Array
     */
    public function bindParameters(ContainerBuilder $container, $name, $config)
    {
        if (is_array($config) && empty($config[0])) {
            foreach ($config as $key => $value) {
                $this->bindParameters($container, $name . '.' . $key, $value);
            }
        } else {
            $container->setParameter($name, $config);
        }
    }
}
