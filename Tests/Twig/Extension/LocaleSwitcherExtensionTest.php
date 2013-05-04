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

use Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Lunetics\LocaleBundle\Twig\Extension\LocaleSwitcherExtension
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitcherExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFunctions()
    {
        $container = new ContainerBuilder();
        $extension = new LocaleSwitcherExtension($container);

        $functions = $extension->getFunctions();

        /** @var \Twig_Function_Method $twigExtension */
        $twigExtension = $functions['locale_switcher'];

        $this->assertInstanceOf('Twig_Function_Method', $twigExtension);
        $callable = $twigExtension->getCallable();
        $this->assertEquals('renderSwitcher', $callable[1]);
        $this->assertEquals(array('html'), $twigExtension->getSafe(new \Twig_Node()));
    }

    public function testGetName()
    {
        $container = new ContainerBuilder();
        $extension = new LocaleSwitcherExtension($container);

        $this->assertEquals('locale_switcher', $extension->getName());
    }

    public function testRenderSwitcher()
    {
        $template = uniqid('template:');

        $router = $this->getMockRouter();

        $request = $this->getMockRequest();
        $request->attributes = $this->getMockParameterBag();

        $query = $this->getMockParameterBag();
        $query
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array()))
        ;

        $request->query = $query;

        $switcherHelper = $this->getMockSwitcherHelper();
        $switcherHelper
            ->expects($this->once())
            ->method('renderSwitch')
            ->will($this->returnValue($template))
        ;

        $container = new ContainerBuilder();
        $container->setParameter('lunetics_locale.switcher.show_current_locale', true);
        $container->setParameter('lunetics_locale.switcher.use_controller', true);
        $container->setParameter('lunetics_locale.allowed_locales', array('en', 'fr'));
        $container->set('request', $request);
        $container->set('router', $router);

        $container->set('lunetics_locale.switcher_helper', $switcherHelper);

        $extension = new LocaleSwitcherExtension($container);
        $this->assertEquals($template, $extension->renderSwitcher());
    }

    protected function getMockRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }

    protected function getMockRouter()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function getMockParameterBag()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
    }

    protected function getMockSwitcherHelper()
    {
        return $this
            ->getMockBuilder('Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
