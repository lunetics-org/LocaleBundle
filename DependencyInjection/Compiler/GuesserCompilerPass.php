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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compilerpass Class
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class GuesserCompilerPass implements CompilerPassInterface
{
    /**
     * Compilerpass for Timezone Guessers
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('lunetics_locale.guesser_manager')) {
            return;
        }

        $definition = $container->getDefinition('lunetics_locale.guesser_manager');
        $taggedServiceIds = $container->findTaggedServiceIds('lunetics_locale.guesser');
        $neededServices = $container->getParameter('lunetics_locale.guessing_order');

        foreach ($taggedServiceIds as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (in_array($attributes['alias'], $neededServices)) {
                    $definition->addMethodCall('addGuesser', array(new Reference($id), $attributes["alias"]));
                }
            }
        }
    }
}

