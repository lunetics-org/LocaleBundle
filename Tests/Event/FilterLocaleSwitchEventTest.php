<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Tests\Event;

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Symfony\Component\HttpFoundation\Request;

class FilterLocaleSwitchEventTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterLocaleSwitchEvent()
    {
        $request = Request::create('/');
        $locale = 'de';
        $filter = new FilterLocaleSwitchEvent($request, $locale);
        $this->assertEquals('/', $filter->getRequest()->getPathInfo());
        $this->assertEquals('de', $filter->getLocale());
    }

    /**
     * @dataProvider invalidType
     */
    public function testThrowsInvalidTypeException($locale)
    {
        $this->setExpectedException('\InvalidArgumentException');
        new FilterLocaleSwitchEvent(Request::create('/'), $locale);
    }

    public function invalidType()
    {
        return array(
          array(123),
          array(''),
          array(null)
        );
    }
}
