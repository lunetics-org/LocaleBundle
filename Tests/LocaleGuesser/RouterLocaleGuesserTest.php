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

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class RouterLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserExtendsInterface()
    {
        $guesser = new RouterLocaleGuesser($this->getMetaValidatorMock());
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testLocaleIsIdentifiedFromRequestQuery()
    {
        $request = $this->getRequestWithLocaleQuery();
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = new RouterLocaleGuesser($metaValidator);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('en')
                ->will($this->returnValue(true));

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals('en', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedFromRequestQuery()
    {
        $request = $this->getRequestWithLocaleQuery();
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = new RouterLocaleGuesser($metaValidator);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('en')
                ->will($this->returnValue(false));

        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsIdentifiedIfCheckQueryIsFalse()
    {
        $request = $this->getRequestWithLocaleParameter();
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = new RouterLocaleGuesser($metaValidator, false);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('en')
                ->will($this->returnValue(true));

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals('en', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIfCheckQueryIsFalse()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = new RouterLocaleGuesser($metaValidator, false);

        $metaValidator->expects($this->never())
                ->method('isAllowed');

        $guesser->guessLocale($request);
        $this->assertEquals(false, $guesser->getIdentifiedLocale());
    }

    private function getRequestWithLocaleParameter($locale = 'en')
    {
        $request = Request::create('/hello-world', 'GET');
        $request->attributes->set('_locale', $locale);

        return $request;
    }

    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create('/hello-world', 'GET');
        $request->query->set('_locale', $locale);

        return $request;
    }

    public function getMetaValidatorMock()
    {
        $mock = $this->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
