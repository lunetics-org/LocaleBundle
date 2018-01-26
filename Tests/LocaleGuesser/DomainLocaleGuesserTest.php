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

use Lunetics\LocaleBundle\LocaleGuesser\DomainLocaleGuesser;
use Lunetics\LocaleBundle\LocaleInformation\DomainLocaleMap;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleGuesserTest extends \PHPUnit_Framework_TestCase
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
        //$this->markTestSkipped();
        $metaValidator = $this->getMockMetaValidator();
        $localeMap = $this->getMockDomainLocaleMap();
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

        $guesser = new DomainLocaleGuesser($metaValidator, $localeMap);

        $this->assertEquals($expected, $guesser->guessLocale($request));
        $this->assertEquals($expectedLocale, $guesser->getIdentifiedLocale());
    }

    /**
     * @return array
     */
    public function dataDomains()
    {
        return array(
            array(false, false, 'localhost', false, false),
            array(true, 'nl_BE', 'dutchversion.be', true, 'nl_BE'),
            array(true, 'en_BE', 'sub.dutchversion.be', true, 'en_BE'),
            array(true, 'fr_BE', 'frenchversion.be', true, 'fr_BE'),
            array(true, 'fr_BE', 'test.frenchversion.be', true, 'fr_BE'),
            //array(true, 'de_CH', 'domain.ch', true, 'de_CH'),
        );
    }

    /**
     * @return mixed
     */
    private function getMockDomainLocaleMap()
    {
        return $this->createMock(DomainLocaleMap::class);
    }

    private function getMockMetaValidator()
    {
        return $this->createMock(MetaValidator::class);
    }

    private function getMockRequest()
    {
        return $this->createMock(Request::class);
    }
}
