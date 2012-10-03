<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class BrowserLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('\Locale')) {
            $this->markTestSkipped('The intl extension can not be found');
        }
    }

    public function testGuesserExtendsInterface()
    {
        $guesser = $this->getGuesser();
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testLocaleIsIdentifiedFromBrowser()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals('fr', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsBrowserPreferencesIsEmpty()
    {
        $request = $this->getRequestWithEmptyBrowserPreferences();
        $guesser = $this->getGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals(false, $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsRetrievedFromSessionIfSet()
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    private function getGuesser($defaultLocale = 'en', $allowedLocales = array('en','fr','de'))
    {
        $guesser = new BrowserLocaleGuesser($defaultLocale, $allowedLocales);

        return $guesser;
    }

    private function getRequestWithBrowserPreferences($locale = 'en')
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithEmptyBrowserPreferences($locale = 'en')
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');

        return $request;
    }

    private function getRequestWithSessionLocale()
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('lunetics_locale', 'ru');
        $request = Request::create('/');
        $request->setSession($session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }
}
