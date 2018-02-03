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

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Twig_SimpleFunction;

/**
 * @covers \Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitcherExtensionTest extends PHPUnit_Framework_TestCase
{
    /** @var RequestStack|MockObject */
    private $mockRequestStack;

    /** @var RouterInterface|MockObject */
    private $mockRouter;

    /** @var AllowedLocalesProvider|MockObject */
    private $mockLocalesProvider;

    /** @var LocaleSwitchHelper|MockObject */
    private $mockSwitcherHelper;

    /** @var bool */
    private $showCurrentLocaleParam;

    /** @var bool */
    private $useControllerParam;

    /** @var LocaleSwitcherExtension */
    private $extension;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->mockRequestStack = $this->createMock(RequestStack::class);
        $this->mockRouter = $this->createMock(RouterInterface::class);
        $this->mockLocalesProvider = $this->createMock(AllowedLocalesProvider::class);
        $this->mockSwitcherHelper = $this->createMock(LocaleSwitchHelper::class);
        $this->showCurrentLocaleParam = true;
        $this->useControllerParam = true;

        $this->constructExtension();
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();

        /** @var Twig_SimpleFunction $twigExtension */
        $twigExtension = current($functions);

        $this->assertInstanceOf('Twig_SimpleFunction', $twigExtension);
        $callable = $twigExtension->getCallable();
        $this->assertEquals('renderSwitcher', $callable[1]);
        $this->assertEquals(array('html'), $twigExtension->getSafe(new \Twig_Node()));
    }

    public function testGetName()
    {
        $this->assertEquals('locale_switcher', $this->extension->getName());
    }

    public function testRenderSwitcher()
    {
        $template = uniqid('template:');

        $request = $this->createMock(Request::class);
        $request->attributes = $this->getMockParameterBag();

        $query = $this->getMockParameterBag();
        $query
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array()))
        ;

        $this->mockRequestStack->expects($this->once())
            ->method('getMasterRequest')
            ->will($this->returnValue($request));

        $request->query = $query;

        $this->mockSwitcherHelper
            ->expects($this->once())
            ->method('renderSwitch')
            ->will($this->returnValue($template))
        ;

        $this->mockLocalesProvider->expects($this->once())
            ->method('getAllowedLocales')->will($this->returnValue(array('en', 'fr')));

        $this->assertEquals($template, $this->extension->renderSwitcher());
    }

    private function constructExtension()
    {
        $this->extension = new LocaleSwitcherExtension(
            $this->mockRequestStack,
            $this->mockRouter,
            $this->mockLocalesProvider,
            $this->mockSwitcherHelper,
            $this->showCurrentLocaleParam,
            $this->useControllerParam
        );
    }

    /**
     * @return MockObject|ParameterBag
     */
    private function getMockParameterBag()
    {
        return $this->createMock(ParameterBag::class);
    }
}
