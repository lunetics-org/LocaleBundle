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

use Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CookieLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var CookieLocaleGuesser */
    private $guesser;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
        $this->guesser   = new CookieLocaleGuesser($this->validator, 'lunetics_locale');
    }

    public function testLocaleIsRetrievedFromCookieIfSet() : void
    {
        $request = $this->getRequest();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(true);

        $this->assertTrue($this->guesser->guessLocale($request));
        $this->assertEquals('ru', $this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromCookieIfSetAndInvalid() : void
    {
        $request = $this->getRequest();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('ru')
            ->willReturn(false);

        $this->assertFalse($this->guesser->guessLocale($request));
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedIfCookieNotSet() : void
    {
        $request = $this->getRequest(false);

        $this->validator
            ->expects($this->never())
            ->method('isAllowed');

        $this->assertFalse($this->guesser->guessLocale($request));
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    private function getRequest($withLocaleCookie = true) : Request
    {
        $request = Request::create('/');
        if ($withLocaleCookie) {
            $request->cookies->set('lunetics_locale', 'ru');
        }

        return $request;
    }
}
