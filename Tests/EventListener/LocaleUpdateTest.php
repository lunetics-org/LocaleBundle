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

use Lunetics\LocaleBundle\LocaleBundleEvents;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
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
        $request = $this->getRequest(false);
        $listener = $this->getLocaleUpdateListener(array('session'), true);

        $this->assertFalse($listener->updateCookie($request, true));
        $this->assertFalse($listener->updateCookie($request, false));

        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);

        $this->assertSame(array(), $addedListeners);
    }

    public function testCookieIsNotUpdatedOnSameLocale()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(true, 'de'));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame(array(), $addedListeners);
    }


    public function testCookieIsUpdatedOnChange()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertContains('updateCookieOnResponse', $addedListeners[0]);
    }

    public function testCookieIsNotUpdatedWithFalseSetCookieOnChange()
    {
        $listener = $this->getLocaleUpdateListener(array('cookie'), false);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame(array(), $addedListeners);
    }

    public function testUpdateCookieOnResponse()
    {
        $event = $this->getEvent($this->getRequest());

        $logger = $this->getMockLogger();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('Locale Cookie set to [ es ]');

        $listener = $this->getLocaleUpdateListener(array(), false, $logger);

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

        $logger = $this->getMockLogger();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('Session var \'lunetics_locale\' set to [ tr ]');

        $listener = $this->getLocaleUpdateListener(array('session'), false, $logger);

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

    public function testGetSubscribedEvents()
    {
        $subcribedEvents = LocaleUpdateListener::getSubscribedEvents();

        $this->assertEquals(array('onLocaleChange'), $subcribedEvents[LocaleBundleEvents::onLocaleChange]);
    }

    private function getFilterLocaleSwitchEvent($withCookieSet = true, $locale = 'fr')
    {
        return new FilterLocaleSwitchEvent($this->getRequest($withCookieSet), $locale);
    }

    private function getLocaleUpdateListener($registeredGuessers = array(), $updateCookie = false, $logger = null)
    {
        $listener = new LocaleUpdateListener($this->getLocaleCookie($updateCookie),
            $this->session,
            $this->dispatcher,
            $registeredGuessers,
            $logger);

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

    private function getMockLogger()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
    }
}
