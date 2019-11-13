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

namespace Lunetics\LocaleBundle\Tests\Event;

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FilterLocaleSwitchEventTest extends TestCase
{
    public function testFilterLocaleSwitchEvent() : void
    {
        $request = Request::create('/');
        $locale  = 'de';
        $filter  = new FilterLocaleSwitchEvent($request, $locale);
        $this->assertEquals('/', $filter->getRequest()->getPathInfo());
        $this->assertEquals('de', $filter->getLocale());
    }

    /**
     * @param mixed
     * @dataProvider invalidType
     */
    public function testThrowsInvalidTypeException($locale) : void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FilterLocaleSwitchEvent(Request::create('/'), $locale);
    }

    public function invalidType()
    {
        return [
            [123],
            [''],
            [null],
        ];
    }
}
