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

use Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CookieLocaleGuesserTest extends TestCase
{

    public function testLocaleIsRetrievedFromCookieIfSet()
    {
        $request = $this->getRequest();
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('ru')
                ->will($this->returnValue(true));

        $guesser = new CookieLocaleGuesser($metaValidator, 'lunetics_locale');

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromCookieIfSetAndInvalid()
    {
        $request = $this->getRequest();
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('ru')
                ->will($this->returnValue(false));

        $guesser = new CookieLocaleGuesser($metaValidator, 'lunetics_locale');

        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedIfCookieNotSet()
    {
        $request = $this->getRequest(false);
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->never())
                ->method('isAllowed');

        $guesser = new CookieLocaleGuesser($metaValidator, 'lunetics_locale');

        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    private function getRequest($withLocaleCookie = true)
    {
        $request = Request::create('/');
        if ($withLocaleCookie) {
            $request->cookies->set('lunetics_locale', 'ru');
        }

        return $request;
    }

    public function getMetaValidatorMock()
    {
        $mock = $this->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
