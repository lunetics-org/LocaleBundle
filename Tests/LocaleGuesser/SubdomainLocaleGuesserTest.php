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

use Lunetics\LocaleBundle\LocaleGuesser\SubdomainLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesserTest extends TestCase
{
    /**
     * @dataProvider dataDomains
     *
     * @param bool $expected
     * @param string $host
     * @param bool $allowed
     */
    public function testGuessLocale($expected, $host, $allowed, $seperator)
    {
        $metaValidator = $this->getMockMetaValidator();

        if (null !== $allowed) {
            $metaValidator
                ->expects($this->once())
                ->method('isAllowed')
                ->will($this->returnValue($allowed));
        }

        $request = $this->getMockRequest();
        $request
            ->expects($this->once())
            ->method('getHost')
            ->will($this->returnValue($host));

        $guesser = new SubdomainLocaleGuesser($metaValidator, $seperator);

        $this->assertEquals($expected, $guesser->guessLocale($request));
    }

    public function dataDomains()
    {
        return [
            [true, 'en.domain', true, null],
            [false, 'fr.domain', false, null],
            [false, 'domain', null, null],
            [false, 'www.domain', false, null],
            [true, 'en-ca.domain', true, '-'],
            [true, 'fr_ca.domain', true, '_'],
            [false, 'de-DE.domain', false, '_'],
        ];
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
