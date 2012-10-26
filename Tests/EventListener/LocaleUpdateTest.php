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

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;

use Lunetics\LocaleBundle\EventListener\LocaleUpdateListener;
use Lunetics\LocaleBundle\Session\LocaleSession;
use Lunetics\LocaleBundle\Cookie\LocaleCookie;

class LocaleUpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;
    /**
     * @var LocaleSession
     */
    private $session;

    public function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->session = new LocaleSession(new Session(new MockArraySessionStorage()));
    }

    public function testCookieIsNotUpdatedNoGuesser()
    {
        $listener = $this->getLocaleUpdateListener(array('session'), false, true);

        $this->assertFalse($listener->updateCookie(true));
        $this->assertFalse($listener->updateCookie(false));

        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent());
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);

        $this->assertSame(array(), $addedListeners);
    }

    public function testCookieIsNotUpdatedOnSameLocale()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), true, true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent('de'));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame(array(), $addedListeners);
    }


    public function testCookieIsUpdatedOnChange()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), false, true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent());
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertContains('updateCookieOnResponse', $addedListeners[0]);
    }

    public function testCookieIsNotUpdatedWithFalseSetCookieOnChange()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), false, false);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent());
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame(array(), $addedListeners);
    }

    public function testUpdateCookieOnResponse()
    {
        $event = $this->getEvent($this->getRequest());
        $listener = $this->getLocaleUpdateListener();

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'es');

        $response = $listener->updateCookieOnResponse($event);

        /** @var $cookie \Symfony\Component\HttpFoundation\Cookie */
        list($cookie) = $response->headers->getCookies();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Cookie', $cookie);
        $this->assertEquals('lunetics_locale', $cookie->getName());
        $this->assertEquals('es', $cookie->getValue());

    }

    public function testUpdateSession()
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(array('session'));

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'tr');

        $this->assertTrue($listener->updateSession());
    }

    public function testNotUpdateSessionNoGuesser()
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(array('cookie'));

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        $this->assertFalse($listener->updateSession());
    }

    public function testNotUpdateSessionSameLocale()
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(array('session'));

        $reflectionClass = new \ReflectionClass($listener);
        $property = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        $this->assertFalse($listener->updateSession());
    }

    private function getFilterLocaleSwitchEvent($locale = 'fr')
    {
        return new \Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent($locale);
    }

    private function getLocaleUpdateListener($registeredGuessers = array(), $withCookieSet = true, $updateCookie = false)
    {
        $listener = new LocaleUpdateListener($this->getLocaleCookie($updateCookie),
            $this->session,
            $this->getRequest($withCookieSet),
            $this->dispatcher,
            $registeredGuessers);

        return $listener;
    }

    private function getEvent(Request $request)
    {
        return new FilterResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST, new Response);
    }


    private function getLocaleCookie($updateCookie)
    {
        $cookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, $updateCookie);

        return $cookie;
    }

    private function getRequest($withCookieSet = false)
    {
        $request = Request::create('/', 'GET', array(), $withCookieSet ? array('lunetics_locale' => 'de') : array());

        return $request;
    }
}
