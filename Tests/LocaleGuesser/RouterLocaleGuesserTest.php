<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class RouterLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserExtendsInterface()
    {
        $guesser = new RouterLocaleGuesser();
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }
    
    public function testLocaleIsIdentifiedFromRequestQuery()
    {
        $request = $this->getRequestWithLocaleQuery();
        $guesser = new RouterLocaleGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals('en', $guesser->getIdentifiedLocale());
    }
    
    public function testLocaleIsNotIdentifiedIfCheckQueryIsFalse()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $guesser = new RouterLocaleGuesser(false);
        $guesser->guessLocale($request);
        $this->assertEquals(false, $guesser->getIdentifiedLocale());
    }
    
    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create('/hello-world', 'GET', array('_locale' => $locale));
        return $request;
    }
}