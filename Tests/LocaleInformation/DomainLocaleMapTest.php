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

use Lunetics\LocaleBundle\LocaleInformation\DomainLocaleMap;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class DomainLocaleMapTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLocale()
    {
        $domainLocaleMap = new DomainLocaleMap(
            array(
                'sub.dutchversion.be' => 'en_BE',
                'dutchversion.be' => 'nl_BE',
                'spanishversion.be' => null,
                'frenchversion.be' => 'fr_BE'
            )
        );

        $this->assertEquals('en_BE', $domainLocaleMap->getLocale('sub.dutchversion.be'));
        $this->assertEquals('nl_BE', $domainLocaleMap->getLocale('dutchversion.be'));
        $this->assertEquals(false, $domainLocaleMap->getLocale('spanishversion.be'));
        $this->assertEquals(false, $domainLocaleMap->getLocale('unknown.be'));
        $this->assertEquals('fr_BE', $domainLocaleMap->getLocale('frenchversion.be'));
    }
} 
