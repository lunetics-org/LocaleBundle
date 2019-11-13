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

use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RouterLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var RouterLocaleGuesser */
    private $guesser;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
        $this->guesser   = new RouterLocaleGuesser($this->validator);
    }

    public function testGuesserExtendsInterface() : void
    {
        $this->assertInstanceOf(LocaleGuesserInterface::class, $this->guesser);
    }

    public function testLocaleIsIdentified() : void
    {
        $request = $this->getRequestWithLocaleParameter();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(true);

        $this->assertTrue($this->guesser->guessLocale($request));
        $this->assertEquals('en', $this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotAllowed() : void
    {
        $request = $this->getRequestWithLocaleParameter();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('en')
            ->willReturn(false);

        $this->assertFalse($this->guesser->guessLocale($request));
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentified() : void
    {
        $request = $this->getRequestWithLocaleQuery('fr');

        $this->validator
            ->expects($this->never())
            ->method('isAllowed');

        $this->guesser
            ->guessLocale($request);

        $this->assertEquals(false, $this->guesser->getIdentifiedLocale());
    }

    private function getRequestWithLocaleParameter($locale = 'en') : Request
    {
        $request = Request::create('/hello-world', 'GET');
        $request->attributes->set('_locale', $locale);

        return $request;
    }

    private function getRequestWithLocaleQuery($locale = 'en') : Request
    {
        $request = Request::create('/hello-world', 'GET');
        $request->query->set('_locale', $locale);

        return $request;
    }
}
