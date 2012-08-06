<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class CookieLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testLocaleIsRetrievedFromCookieIfSet()
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals('ru', $guesser->getIdentifiedLocale());
    }
    
    private function getGuesser($defaultLocale = 'en', $allowedLocales = array('en','fr','de'))
    {
        $guesser = new CookieLocaleGuesser('lunetics_locale');
        return $guesser;
    }
    
    private function getRequestWithSessionLocale()
    {
        $request = Request::create('/');
        $request->cookies->set('lunetics_locale', 'ru');
        return $request;
    }
}