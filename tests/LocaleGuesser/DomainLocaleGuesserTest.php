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

use Lunetics\LocaleBundle\LocaleGuesser\DomainLocaleGuesser;
use Lunetics\LocaleBundle\LocaleInformation\DomainLocaleMap;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;
    /** @var DomainLocaleMap|MockObject */
    private $domainLocaleMap;
    /** @var DomainLocaleGuesser */
    private $guesser;

    public function setUp() : void
    {
        $this->validator       = $this->createMock(MetaValidator::class);
        $this->domainLocaleMap = $this->createMock(DomainLocaleMap::class);
        $this->guesser         = $guesser = new DomainLocaleGuesser($this->validator, $this->domainLocaleMap);

    }

    /**
     * @dataProvider dataDomains
     */
    public function testGuessLocale(bool $expected, string $expectedLocale, string $host, bool $allowed, string $mappedLocale) : void
    {
        $this->domainLocaleMap
            ->method('getLocale')
            ->willReturn($mappedLocale);

        $this->validator
            ->expects($allowed ? $this->once() : $this->never())
            ->method('isAllowed')
            ->willReturn($allowed);

        $request = $this->createMock(Request::class);

        $request->expects($this->once())
            ->method('getHost')
            ->willReturn($host);

        $this->assertEquals($expected, $this->guesser->guessLocale($request));
        $this->assertEquals($expectedLocale, $this->guesser->getIdentifiedLocale());
    }

    /**
     * @return array
     */
    public function dataDomains() : array
    {
        return [
            [false, false, 'localhost', false, false],
            [true, 'nl_BE', 'dutchversion.be', true, 'nl_BE'],
            [true, 'en_BE', 'sub.dutchversion.be', true, 'en_BE'],
            [true, 'fr_BE', 'frenchversion.be', true, 'fr_BE'],
            [true, 'fr_BE', 'test.frenchversion.be', true, 'fr_BE'],
            [true, 'de_CH', 'domain.ch', true, 'de_CH'],
        ];
    }
}
