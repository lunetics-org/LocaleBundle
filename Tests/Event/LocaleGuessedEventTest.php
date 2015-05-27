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

use Lunetics\LocaleBundle\Event\LocaleGuessedEvent;

class LocaleGuessedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testLocaleGuessedEvent()
    {
        $guesser = 'Router';
        $locale = 'de';
        $event = new LocaleGuessedEvent($guesser, $locale);
        $this->assertEquals('Router', $filter->getGuesser());
        $this->assertEquals('de', $filter->getLocale());
    }
    /**
     * @dataProvider invalidType
     */
    public function testThrowsInvalidTypeException($guesser, $locale)
    {
        $this->setExpectedException('\InvalidArgumentException');
        new LocaleGuessedEvent($guesser, $locale);
    }
    
    public function invalidType()
    {
        return array(
          array(123 , 123),
          array(''  , '' ),
          array(null, null),
          array(123 , 'de'),
          array(''  , 'de'),
          array(null, 'de'),
          array('Router', 'de'),
          array('Router', ''  ),
          array('Router', null)
        );
    }
}
