<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\Templating\Helper;

use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitchHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderSwitch()
    {
        $template = uniqid('template:');

        $templating = $this->getMockEngineInterface();
        $templating
            ->expects($this->once())
            ->method('render')
            ->with($template, array())
            ->will($this->returnValue($template))
        ;

        $localeSwitchHelper = new LocaleSwitchHelper($templating, $template);

        $this->assertEquals($template, $localeSwitchHelper->renderSwitch());
    }

    public function testGetName()
    {
        $templating = $this->getMockEngineInterface();

        $localeSwitchHelper = new LocaleSwitchHelper($templating, null);

        $this->assertEquals('locale_switch_helper', $localeSwitchHelper->getName());
    }

    protected function getMockEngineInterface()
    {
        return $this->getMock('Symfony\Component\Templating\EngineInterface');
    }
}
