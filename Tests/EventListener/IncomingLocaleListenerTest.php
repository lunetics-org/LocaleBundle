<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\EventListener\IncomingLocaleListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class IncomingLocaleListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testNotAllowedLocale()
    {
    	$listener = new IncomingLocaleListener(array('de'));

    	$request = Request::create('/', 'GET');
    	$request->setLocale('fr');

    	$event = $this->getEvent($request);
    	$listener->onKernelRequest($event);
    }

    public function testAllowedLocale()
    {
        $listener = new IncomingLocaleListener(array('de'));

        $request = Request::create('/', 'GET');
        $request->setLocale('de');

        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
    }

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }

}
