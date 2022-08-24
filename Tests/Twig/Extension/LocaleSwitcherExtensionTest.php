<?php

/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Tests\Twig\Extension;

use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension;
use PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * @covers \Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitcherExtensionTest extends TestCase
{
    private $targetInformationBuildMock;
    private $localeSwitcherHelperMock;

    public function setUp()
    {
        $this->targetInformationBuildMock = $this->createMock(TargetInformationBuilder::class);
        $this->localeSwitcherHelperMock = $this->createMock(LocaleSwitchHelper::class);
    }

    public function testGetFunctions()
    {
        $extension = new LocaleSwitcherExtension($this->targetInformationBuildMock, $this->localeSwitcherHelperMock);

        $functions = $extension->getFunctions();

        /** @var TwigFunction $twigExtension */
        $twigExtension = current($functions);

        $this->assertInstanceOf('\Twig\TwigFunction', $twigExtension);
        $callable = $twigExtension->getCallable();
        $this->assertEquals('renderSwitcher', $callable[1]);
        $this->assertEquals(array('html'), $twigExtension->getSafe(new Node()));
    }

    public function testGetName()
    {
        $extension = new LocaleSwitcherExtension($this->targetInformationBuildMock, $this->localeSwitcherHelperMock);

        $this->assertEquals('locale_switcher', $extension->getName());
    }

    public function testRenderSwitcher()
    {
        $template = uniqid('template:');
        $this->localeSwitcherHelperMock
            ->expects($this->once())
            ->method('renderSwitch')
            ->will($this->returnValue($template));
        $this->targetInformationBuildMock
            ->expects($this->once())
            ->method('getTargetInformations')
            ->with($this->equalTo(null), $this->equalTo([]))
            ->will($this->returnValue([]));

        $extension = new LocaleSwitcherExtension($this->targetInformationBuildMock, $this->localeSwitcherHelperMock);

        $this->assertEquals($template, $extension->renderSwitcher());
    }
}
