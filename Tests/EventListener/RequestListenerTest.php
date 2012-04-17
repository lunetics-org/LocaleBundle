<?php

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\EventListener\RequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Lunetics\LocaleBundle\LocaleDetection\DetectionPriority;
use Symfony\Component\HttpKernel\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;


class RequestListenerTest extends \PHPUnit_Framework_TestCase
{

    const BROWSER_CLASS = 'Lunetics\LocaleBundle\LocaleDetection\BrowserLocaleDetector';
    const ROUTER_CLASS  = 'Lunetics\LocaleBundle\LocaleDetection\RouterLocaleDetector';

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }

    /**
    * Basic test :
    * Router param _locale
    * No browser preferred language
    * The browser locale detection must fail to find a locale
    * The router has to take over
    * The router has to find a locale based on the _locale param
    */
    public function testLocaleWithRouteParamsAndDefaultPrio()
    {
        $request = Request::create('/');
        $request->attributes->set('_locale', 'es');
        $request->headers->set('Accept-language', '');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->assertEquals('', $request->getPreferredLanguage()); // Checks that the accept-language is well overwritten with blank value
        $listener = $this->getListener();
        $listener->onKernelRequest($this->getEvent($request));
        $this->assertEquals('es', $request->getLocale());
    }

    /**
    * None locale from browser
    * None from route
    * Expected default "en" locale
    */
    public function testLocaleWithNihilParams()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $this->assertEquals('', $request->getPreferredLanguage()); // Checks that the accept-language is well overwritten with blank value
        $listener = $this->getListener();
        $listener->onKernelRequest($this->getEvent($request));
        $this->assertEquals('en', $request->getLocale());
    }

    /**
    * Scenario : There is no route _locale param
    * The browser contains a Accept-Language header
    * The expected locale is the browser preferred language
    */
    public function testLocaleWithBrowserLanguage()
    {
        $request = new Request();
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $listener = $this->getListener();
        $listener->onKernelRequest($this->getEvent($request));

        $this->assertEquals('fr_FR', $request->getLocale());
    }

    public function testLocaleWithBrowserLanguageAndRouterPrio()
    {
        $request = new Request();
        $request->attributes->set('_locale', 'de');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $listener = $this->getListener(array('router'));
        $listener->onKernelRequest($this->getEvent($request));
        $this->assertEquals('de', $request->getLocale());
    }

    /**
    * Scenario:
    * The route contains a _locale parameter
    * The priority is first set to the browser
    * The expected locale is the browser preferred language
    */
    public function testLocaleWithRouterParamAndBrowserPrio()
    {
        $request = new Request();
        $request->attributes->set('_locale', 'ru');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        $request->setSession(new Session(new MockArraySessionStorage()));

        $listener = $this->getListener(array('browser'));
        $listener->onKernelRequest($this->getEvent($request));

        // Asserts that the locale is the browser preferred Language
        $this->assertEquals('fr_FR', $request->getLocale());
    }

    /**
    * Use case:
    * We have a cookie set to "de" locale
    * We are visiting a resource with a "fr" router param
    * Does the cookie needs to be update at each new detection ?
    */
    public function testWithCookieAndRouterParam()
    {
        $request = new Request();
        $request->attributes->set('_locale', 'fr');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        $request->setSession(new Session(new MockArraySessionStorage()));
        $request->cookies->set('_locale', 'de');
        $listener = $this->getListener(array('router'));
        $listener->onKernelRequest($this->getEvent($request));


        // Asserts that the locale is the browser preferred Language
        $this->assertEquals('fr', $request->getLocale());
    }

    private function getListener($prio = array())
    {
    	$prioClass = new DetectionPriority($prio, self::BROWSER_CLASS, self::ROUTER_CLASS);
        $listener = new RequestListener($prioClass, 'en', array(), new NullLogger());
        $listener->setEventDispatcher(new EventDispatcher);
        return $listener;
    }
}