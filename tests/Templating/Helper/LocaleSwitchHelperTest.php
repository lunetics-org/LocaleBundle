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

namespace Lunetics\LocaleBundle\Tests\Templating\Helper;

use Lunetics\LocaleBundle\Templating\Helper\LocaleSwitchHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Templating\EngineInterface;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSwitchHelperTest extends TestCase
{
    public function testRenderSwitch() : void
    {
        $template = uniqid('template:', true);

        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects($this->once())
            ->method('render')
            ->with($template, [])
            ->willReturn($template);

        $localeSwitchHelper = new LocaleSwitchHelper($templating, $template);

        $this->assertEquals($template, $localeSwitchHelper->renderSwitch());
    }
}
