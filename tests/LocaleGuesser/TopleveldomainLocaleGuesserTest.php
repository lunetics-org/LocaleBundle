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

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\TopleveldomainLocaleGuesser;
use Lunetics\LocaleBundle\LocaleInformation\TopleveldomainLocaleMap;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopleveldomainLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var TopleveldomainLocaleMap|MockObject */
    private $localeMap;

    /** @var TopleveldomainLocaleGuesser */
    private $guesser;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
        $this->localeMap = $this->createMock(TopleveldomainLocaleMap::class);
        $this->guesser   = new TopleveldomainLocaleGuesser($this->validator, $this->localeMap);
    }

    /**
     * @dataProvider dataDomains
     */
    public function testGuessLocale(bool $expected, string $expectedLocale, string $host, bool $allowed, string $mappedLocale) : void
    {
        $this->localeMap
            ->method('getLocale')
            ->willReturn($mappedLocale);

        $this->validator
            ->expects($allowed ? $this->once() : $this->never())
            ->method('isAllowed')
            ->willReturn($allowed);

        $request = $this->getMockRequest();
        $request->expects($this->once())
            ->method('getHost')
            ->willReturn($host);

        $this->assertEquals($expected, $this->guesser->guessLocale($request));
        $this->assertEquals($expectedLocale, $this->guesser->getIdentifiedLocale());
    }

    public function dataDomains() : array
    {
        return [
            'double dot + sublocale'       => [false, false, 'localhost', false, false],
            'double dot + sublocale 1'     => [true, 'en_GB', 'domain.co.uk', true, 'en_GB'],
            'single dot tld + sublocale'   => [true, 'de_CH', 'domain.ch', true, 'de_CH'],
            'not allowed'                  => [false, false, 'domain.fr', false, false],
            'no tld'                       => [false, false, 'domain', false, false],
            'wrong/not allowed tld'        => [false, false, 'domain.doom', false, false],
            'normal tld to locale mapping' => [true, 'de', 'domain.de', true, 'de'],
        ];
    }

    private function getMockRequest()
    {
        return $this->createMock(Request::class);
    }
}
