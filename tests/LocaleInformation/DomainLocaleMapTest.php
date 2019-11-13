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

namespace Lunetics\LocaleBundle\Tests\LocaleInformation;

use Lunetics\LocaleBundle\LocaleInformation\DomainLocaleMap;
use PHPUnit\Framework\TestCase;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class DomainLocaleMapTest extends TestCase
{
    public function testGetLocale() : void
    {
        $domainLocaleMap = new DomainLocaleMap(
            [
                'sub.dutchversion.be' => 'en_BE',
                'dutchversion.be'     => 'nl_BE',
                'spanishversion.be'   => null,
                'frenchversion.be'    => 'fr_BE',
            ]
        );

        $this->assertEquals('en_BE', $domainLocaleMap->getLocale('sub.dutchversion.be'));
        $this->assertEquals('nl_BE', $domainLocaleMap->getLocale('dutchversion.be'));
        $this->assertEquals(false, $domainLocaleMap->getLocale('spanishversion.be'));
        $this->assertEquals(false, $domainLocaleMap->getLocale('unknown.be'));
        $this->assertEquals('fr_BE', $domainLocaleMap->getLocale('frenchversion.be'));
    }
}
