<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests;

use Lunetics\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use Lunetics\LocaleBundle\DependencyInjection\Compiler\RouterResourcePass;
use Lunetics\LocaleBundle\LuneticsLocaleBundle;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LuneticsLocaleBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMockContainer();

        $container
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with(new GuesserCompilerPass())
        ;

        $container
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with(new RouterResourcePass())
        ;

        $bundle = new LuneticsLocaleBundle();
        $bundle->build($container);
    }

    protected function getMockContainer()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
    }
}
