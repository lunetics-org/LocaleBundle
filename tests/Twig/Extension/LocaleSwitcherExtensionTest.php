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

namespace Lunetics\LocaleBundle\Tests\Twig\Extension;

use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Twig_Node;

/**
 * @covers \Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitcherExtensionTest extends TestCase
{
    /** @var TargetInformationBuilder|MockObject */
    private $targetInformationBuilder;

    /** @var LocaleSwitchHelper|MockObject */
    private $localeSwitcherHelper;

    public function setUp() : void
    {
        $this->targetInformationBuilder = $this->createMock(TargetInformationBuilder::class);
        $this->localeSwitcherHelper     = $this->createMock(LocaleSwitchHelper::class);
    }

    public function testGetFunctions() : void
    {
        $extension = new LocaleSwitcherExtension($this->targetInformationBuilder, $this->localeSwitcherHelper);

        $functions = $extension->getFunctions();

        /** @var TwigFunction $twigExtension */
        $twigExtension = current($functions);

        $this->assertInstanceOf('Twig_SimpleFunction', $twigExtension);
        $callable = $twigExtension->getCallable();
        $this->assertEquals('renderSwitcher', $callable[1]);
        $this->assertEquals(['html'], $twigExtension->getSafe(new Twig_Node()));
    }

    public function testRenderSwitcher() : void
    {
        $template = uniqid('template:', true);

        $this->localeSwitcherHelper
            ->expects($this->once())
            ->method('renderSwitch')
            ->willReturn($template);

        $this->targetInformationBuilder
            ->expects($this->once())
            ->method('getTargetInformations')
            ->with($this->equalTo(null), $this->equalTo([]))
            ->willReturn([]);

        $extension = new LocaleSwitcherExtension($this->targetInformationBuilder, $this->localeSwitcherHelper);

        $this->assertEquals($template, $extension->renderSwitcher());
    }
}
