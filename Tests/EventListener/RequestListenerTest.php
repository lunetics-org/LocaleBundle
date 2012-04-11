<?php

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\EventListener\RequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Lunetics\LocaleBundle\LocaleDetection\DetectionPriority;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }

    public function testLocaleSetForRoutingContext()
    {
        if (!class_exists('Symfony\Component\Routing\Router')) {
            $this->markTestSkipped('The "Routing" component is not available');
        }

        $request = Request::create('/');
        $request->attributes->set('_locale', 'es');

        $listener = $this->getListener();
        $listener->onKernelRequest($this->getEvent($request));

        $this->assertEquals('es', $request->getLocale());
    }

    private function getListener()
    {
    	$prioClass = new DetectionPriority(array(), null, 'Lunetics\LocaleBundle\LocaleDetection\RouterLocaleDetector');
        return $listener = new RequestListener($prioClass);
    }
}