<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lunetics\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use Lunetics\LocaleBundle\DependencyInjection\Compiler\RouterResourcePass;
use Lunetics\LocaleBundle\DependencyInjection\Compiler\RemoveSessionPass;

/**
 * LocaleBundle
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LuneticsLocaleBundle extends Bundle
{
    /**
     * Add CompilerPass
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GuesserCompilerPass);
        $container->addCompilerPass(new RouterResourcePass);
        $container->addCompilerPass(new RemoveSessionPass);
    }
}
