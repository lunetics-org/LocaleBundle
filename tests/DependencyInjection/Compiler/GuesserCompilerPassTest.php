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

use Lunetics\LocaleBundle\DependencyInjection\Compiler\GuesserCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class GuesserCompilerPassTest extends TestCase
{
    public function testProcess() : void
    {
        $container = new ContainerBuilder();

        $container
            ->register('lunetics_locale.guesser_manager');

        $container
            ->register('lunetics_locale.query_guesser')
            ->addTag('lunetics_locale.guesser', ['alias' => 'query']);

        $container
            ->register('lunetics_locale.browser_guesser')
            ->addTag('lunetics_locale.guesser', ['alias' => 'browser']);

        $container->setParameter('lunetics_locale.guessing_order', ['query']);

        $this->process($container);

        $methodCalls = $container
            ->getDefinition('lunetics_locale.guesser_manager')
            ->getMethodCalls();

        $this->assertCount(
            1,
            $methodCalls
        );

        $methodName = $methodCalls[0][0];
        $argument   = $methodCalls[0][1][1];

        $this->assertEquals('addGuesser', $methodName);
        $this->assertEquals('query', $argument);
    }

    protected function process(ContainerBuilder $container) : void
    {
        $pass = new GuesserCompilerPass();
        $pass->process($container);
    }
}
