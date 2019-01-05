<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleInformation;

use Lunetics\LocaleBundle\LocaleInformation\TopleveldomainLocaleMap;
use PHPUnit\Framework\TestCase;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopleveldomainLocaleMapTest extends TestCase
{
    public function testGetLocale()
    {
        $tldLocaleMap = new TopleveldomainLocaleMap(array('net' => 'de',
                                                          'org' => null,
                                                          'com' => 'en_US',
                                                          'uk' => 'en_GB',
                                                          'be' => 'fr_BE'));

        $this->assertEquals('en_GB', $tldLocaleMap->getLocale('uk'));
        $this->assertEquals('en_US', $tldLocaleMap->getLocale('com'));
        $this->assertEquals('de', $tldLocaleMap->getLocale('net'));
        $this->assertEquals(false, $tldLocaleMap->getLocale('fr'));
        $this->assertEquals(false, $tldLocaleMap->getLocale('org'));
        $this->assertEquals('fr_BE', $tldLocaleMap->getLocale('be'));
    }
}
