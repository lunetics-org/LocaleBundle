<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\DependencyInjection\Compiler;

use Lunetics\LocaleBundle\DependencyInjection\Compiler\RemoveSessionPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class RemoveSessionPassTest extends TestCase
{
    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function getContainer()
    {
        $container = new ContainerBuilder();

        $container->register('lunetics_locale.session_guesser');
        $container->register('lunetics_locale.locale_session');

        return $container;
    }

    public function testSessionPresent()
    {
        $container = $this->getContainer();

        $container->register('session');

        $this->process($container);

        $this->assertTrue($container->hasDefinition('lunetics_locale.session_guesser'));
        $this->assertTrue($container->hasDefinition('lunetics_locale.locale_session'));
    }

    public function testSessioAbsent()
    {
        $container = $this->getContainer();

        $this->process($container);

        $this->assertFalse($container->hasDefinition('lunetics_locale.session_guesser'));
        $this->assertFalse($container->hasDefinition('lunetics_locale.locale_session'));
    }

    protected function process(ContainerBuilder $container)
    {
        $pass = new RemoveSessionPass();
        $pass->process($container);
    }
}
