<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsIdentifiedFromBrowserTestFallbackForNoIntlExtension()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser();
        $reflectionClass = new \ReflectionClass($guesser);
        $property = $reflectionClass->getProperty('intlExtension');
        $property->setAccessible(true);
        $property->setValue($guesser, false);
        $guesser->guessLocale($request);
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsBrowserPreferencesIsEmpty()
    {
        $request = $this->getRequestWithEmptyBrowserPreferences();
        $guesser = $this->getGuesser();
        $guesser->guessLocale($request);
        $this->assertEquals(false, $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsBrowserPreferencesIsEmptyTestFallbackForNoIntlExtension()
    {
        $request = $this->getRequestWithEmptyBrowserPreferences();
        $guesser = $this->getGuesser();
        $reflectionClass = new \ReflectionClass($guesser);
        $property = $reflectionClass->getProperty('intlExtension');
        $property->setAccessible(true);
        $property->setValue($guesser, false);
        $guesser->guessLocale($request);
        $this->assertEquals(false, $guesser->getIdentifiedLocale());
    }

    public function testRestrictionWithLocalesOnly()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser('en', array('fr_FR', 'en_US'));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testRestrictionWithLocalesOnlyNotIdentified()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser('en', array('fr_CH', 'en_GB'));
        $guesser->guessLocale($request);
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testReturnCorrectLocaleForLanguage()
    {
        $request = $this->getRequestWithBrowserPreferencesMultipleLangLocales();
        $guesser = $this->getGuesser('en', array('fr_FR', 'en_US'));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testEnsureCorrectLocaleForAllowedLocales()
    {
        $request = $this->getRequestWithBrowserPreferencesMultipleLangLocales();

        $guesser = $this->getGuesser('en', array('de', 'en_GB'));
        $guesser->guessLocale($request);
        $this->assertEquals('en_GB', $guesser->getIdentifiedLocale());

        $guesser = $this->getGuesser('en', array('fr', 'en_GB'));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_CH', $guesser->getIdentifiedLocale());

        $guesser = $this->getGuesser('en', array('en_GB', 'fr'));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_CH', $guesser->getIdentifiedLocale());

        $guesser = $this->getGuesser('en', array('fr_FR', 'en'));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());

        $guesser = $this->getGuesser('en', array('en', 'fr_FR',));
        $guesser->guessLocale($request);
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguage()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser('en', array('ar', 'es'));
        $guesser->guessLocale($request);
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguageTestFallbackForNoIntlExtension()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser('en', array('ar', 'es'));
        $reflectionClass = new \ReflectionClass($guesser);
        $property = $reflectionClass->getProperty('intlExtension');
        $property->setAccessible(true);
        $property->setValue($guesser, false);
        $guesser->guessLocale($request);
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    private function getGuesser($defaultLocale = 'en', $allowedLocales = array('en', 'fr', 'de'))
    {
        $guesser = new BrowserLocaleGuesser($defaultLocale, $allowedLocales);

        return $guesser;
    }

    private function getRequestWithBrowserPreferences()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithBrowserPreferencesMultipleLangLocales()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-CH,fr;q=0.8,fr-FR;q=0.7, en-GB;q=0.6,en;q=0.5');

        return $request;
    }

    private function getRequestWithEmptyBrowserPreferences()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');

        return $request;
    }
}
