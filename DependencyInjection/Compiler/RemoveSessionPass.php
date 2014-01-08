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
 * This pass remove session dependencies
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class RemoveSessionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('session')) {
            return;
        }
        $container->removeDefinition('lunetics_locale.session_guesser');
        $container->removeDefinition('lunetics_locale.locale_session');

    }
}