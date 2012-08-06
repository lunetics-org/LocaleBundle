<?php

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\EventListener\LocaleListener;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
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

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }
    
    private function getGuesserManager()
    {
        $routerGuesser = new RouterLocaleGuesser();
        $guessingOrder = array(1 => 'router', 2 => 'browser');
        $manager = new LocaleGuesserManager($guessingOrder, $routerGuesser);
        return $manager;
    }
}