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

use Lunetics\LocaleBundle\LocaleGuesser\SubdomainLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
    }

    /**
     * @dataProvider dataDomains
     */
    public function testGuessLocale(bool $expected, string $host, bool $allowed, ?string $seperator, string $expectedLocale) : void
    {
        $this->validator
            ->method('isAllowed')
            ->willReturn($allowed);

        $request = $this->getMockRequest();

        $request
            ->expects($this->once())
            ->method('getHost')
            ->willReturn($host);

        $guesser = new SubdomainLocaleGuesser($this->validator, $seperator);

        $this->assertEquals($expected, $guesser->guessLocale($request));
        $this->assertEquals($guesser->getIdentifiedLocale(), $expectedLocale);
    }

    public function dataDomains() : array
    {
        return [
            [true, 'en.domain', true, null, 'en'],
            [false, 'fr.domain', false, null, false],
            [false, 'domain', false, null, false],
            [false, 'www.domain', false, null, false],
            [true, 'en-ca.domain', true, '-', 'en_ca'],
            [true, 'fr_ca.domain', true, '_', 'fr_ca'],
            [false, 'de-DE.domain', false, '_', false],
        ];
    }

    private function getMockRequest()
    {
        return $this->createMock(Request::class);
    }
}
