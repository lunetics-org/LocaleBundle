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
    
    public function testRouteLocaleIsReturnedIfRouterIsPrio1()
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager();
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('es', $request->getLocale());
    }
    
    public function testBrowserLocaleIsReturnedIfBrowserIsPrio1()
    {
        $request = $this->getFullRequest();
        $manager = $this->getGuesserManager(array(1 => 'browser', 2 => 'router'));
        $listener = new LocaleListener('en', $manager);
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
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
    
    private function getFullRequest()
    {
        $request = Request::create('/');
        $request->attributes->set('_locale', 'es');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
        return $request;
    }
}