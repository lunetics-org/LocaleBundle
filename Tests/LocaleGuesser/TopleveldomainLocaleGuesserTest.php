<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\TopleveldomainLocaleGuesser;
use Lunetics\LocaleBundle\LocaleInformation\TopleveldomainLocaleMap;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopleveldomainLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataDomains
     *
     * @param bool $expected
     * @param string $host
     * @param bool $allowed
     * @param string $mappedLocale
     */
    public function testGuessLocale($expected, $expectedLocale, $host, $allowed, $mappedLocale)
    {
        $metaValidator = $this->getMockMetaValidator();
        $localeMap = $this->getMockTopleveldomainLocaleMap();
        $localeMap->expects($this->any())
                                 ->method('getLocale')
                                 ->will($this->returnValue($mappedLocale));

        if ($allowed) {
            $metaValidator->expects($this->once())
                          ->method('isAllowed')
                          ->will($this->returnValue($allowed));
        }

        $request = $this->getMockRequest();
        $request->expects($this->once())
                ->method('getHost')
                ->will($this->returnValue($host));

        $guesser = new TopleveldomainLocaleGuesser($metaValidator, $localeMap);

        $this->assertEquals($expected, $guesser->guessLocale($request));
        $this->assertEquals($expectedLocale, $guesser->getIdentifiedLocale());
    }

    /**
     * @return array
     */
    public function dataDomains()
    {
        return array(
            array(true, 'en_GB', 'domain.co.uk', true, 'en_GB'), // double dot + sublocale
            array(true, 'de_CH', 'domain.ch', true, 'de_CH'), // single dot tld + sublocale
            array(false, false, 'domain.fr', false, false), //not allowed
            array(false, false, 'domain', null, false), //no tld
            array(false, false, 'domain.doom', false, false), //wrong/not allowed tld
            array(true, 'de', 'domain.de', true, false), //normal tld to locale mapping
        );
    }

    private function getMockTopleveldomainLocaleMap()
    {
        return $this
            ->getMockBuilder('\Lunetics\LocaleBundle\LocaleInformation\TopleveldomainLocaleMap')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMetaValidator()
    {
        return $this
            ->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }
} 