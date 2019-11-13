<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\DependencyInjection\Compiler;

use Lunetics\LocaleBundle\DependencyInjection\Compiler\RemoveSessionPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class RemoveSessionPassTest extends TestCase
{
    private function getContainer() : ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->register('lunetics_locale.session_guesser');
        $container->register('lunetics_locale.locale_session');

        return $container;
    }

    public function testSessionPresent() : void
    {
        $container = $this->getContainer();

        $container->register('session');

        $this->process($container);

        $this->assertTrue($container->hasDefinition('lunetics_locale.session_guesser'));
        $this->assertTrue($container->hasDefinition('lunetics_locale.locale_session'));
    }

    public function testSessioAbsent() : void
    {
        $container = $this->getContainer();

        $this->process($container);

        $this->assertFalse($container->hasDefinition('lunetics_locale.session_guesser'));
        $this->assertFalse($container->hasDefinition('lunetics_locale.locale_session'));
    }

    protected function process(ContainerBuilder $container) : void
    {
        $pass = new RemoveSessionPass();
        $pass->process($container);
    }
}
