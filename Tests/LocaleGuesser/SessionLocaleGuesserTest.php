<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\SessionLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    protected function setUp()
    {
        if (!class_exists('\Locale')) {
            $this->markTestSkipped('The intl extension can not be found');
        }
    }

    public function testGuesserExtendsInterface()
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser($request->getSession());
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testLocaleIsRetrievedFromSessionIfSet()
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser($request->getSession());
        $guesser->guessLocale($request);
        $this->assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    private function getGuesser($session = null)
    {
        return new SessionLocaleGuesser($session);
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

    private function getSession()
    {
        return new Session(new MockArraySessionStorage());
    }
}
