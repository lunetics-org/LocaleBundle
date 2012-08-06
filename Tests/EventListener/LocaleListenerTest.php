<?php

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\EventListener\LocaleListener;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultLocaleWithoutParams()
    {
        $listener = new LocaleListener('fr', $this->getGuesserManager());
        $event = $this->getEvent($request = Request::create('/'));

        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }
    
    public function testCustomLocaleIsSetWhenParamsExist()
    {
        $listener = new LocaleListener('fr', $this->getGuesserManager());
        $event = $this->getEvent($request = Request::create('/', 'GET', array('_locale' => 'de')));

        $listener->onKernelRequest($event);
        $this->assertEquals('de', $request->getLocale());
    }
    
    /**
     * Router is prio 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testRouteLocaleIsReturnedIfRouterIsPrio1()
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager();
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('es', $request->getLocale());
    }
    
    /**
     * Browser is prio 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserLocaleIsReturnedIfBrowserIsPrio1()
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager(array(1 => 'browser', 2 => 'router'));
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
    }
    
    /**
     * Router is prio 1
     * Request DOES NOT contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserTakeOverIfRouterParamsFail()
    {
        $request = $this->getFullRequest(null);
        $manager = $this->getGuesserManager();
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
    }
    
    /**
     * Router is prio 1
     * Request DOES NOT contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testThatGuesserIsNotCalledIfNotInGuessingOrder()
    {
        $request = $this->getRequestWithRouterParam();
        $manager = $this->getGuesserManager(array(1 => 'browser'));
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('en', $request->getLocale());
    }
    
    /**
     * Request with empty route params and empty browser preferences
     */
    public function testDefaultLocaleIfEmptyRequest()
    {
        $request = $this->getEmptyRequest();
        $manager = $this->getGuesserManager();
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('en', $request->getLocale());
    }

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }
    
    private function getGuesserManager($order = array(1 => 'router', 2 => 'browser'))
    {
        $defaultLocale = 'en';
        $allowedLocales = array('de', 'fr', 'nl');
        $routerGuesser = new RouterLocaleGuesser();
        $browserGuesser = new BrowserLocaleGuesser($defaultLocale, $allowedLocales);
        $manager = new LocaleGuesserManager($order, $routerGuesser, $browserGuesser);
        return $manager;
    }
    
    private function getRequestWithRouterParam($routerLocale = 'es')
    {
        $request = Request::create('/');
        if(!empty($routerLocale)){
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', '');
        return $request;
    }
    
    private function getFullRequest($routerLocale = 'es')
    {
        $request = Request::create('/');
        if(!empty($routerLocale)){
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        return $request;
    }
    
    private function getEmptyRequest()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        return $request;
    }
}