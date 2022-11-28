<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This pass adds the Lunetics Locale route when lunetics_locale.switcher.use_controller is true.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class RouterResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('lunetics_locale.switcher.use_controller') || !$container->getParameter('router.resource')) {
            return;
        }

        $file = $container->getParameter('kernel.cache_dir').'/lunetics_locale/routing.yml';

        if (!is_dir($dir = dirname($file))) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($file, Yaml::dump(array(
            '_lunetics_locale' => array('resource' => '@LuneticsLocaleBundle/Resources/config/routing.yml', 'prefix' => '/'),
            '_app'     => array('resource' => $container->getParameter('router.resource'))
        )));

        $container->setParameter('router.resource', $file);
    }
}