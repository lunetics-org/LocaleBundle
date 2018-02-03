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

use Lunetics\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\Validator\MetaValidator;

class QueryLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserExtendsInterface()
    {
        $guesser = new QueryLocaleGuesser($this->getMetaValidatorMock());
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testLocaleIsIdentifiedFromRequestQuery()
    {
        $request = $this->getRequestWithLocaleQuery();
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = new QueryLocaleGuesser($metaValidator);

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
        $guesser = new QueryLocaleGuesser($metaValidator);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('en')
                ->will($this->returnValue(false));

        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create('/hello-world', 'GET');
        $request->query->set('_locale', $locale);

        return $request;
    }

    public function getMetaValidatorMock()
    {
        return $this->createMock(MetaValidator::class);
    }
}
