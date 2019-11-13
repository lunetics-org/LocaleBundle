<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\EventListener\LocaleUpdateListener;
use Lunetics\LocaleBundle\Session\LocaleSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleUpdateTest extends TestCase
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
        $this->session    = new LocaleSession(new Session(new MockArraySessionStorage()));
    }

    public function testCookieIsNotUpdatedNoGuesser()
    {
        $request  = $this->getRequest(false);
        $listener = $this->getLocaleUpdateListener(['session'], true);

        $this->assertFalse($listener->updateCookie($request, true));
        $this->assertFalse($listener->updateCookie($request, false));

        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);

        $this->assertSame([], $addedListeners);
    }

    public function testCookieIsNotUpdatedOnSameLocale()
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(true, 'de'));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame([], $addedListeners);
    }


    public function testCookieIsUpdatedOnChange()
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], true);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertContains('updateCookieOnResponse', $addedListeners[0]);
    }

    public function testCookieIsNotUpdatedWithFalseSetCookieOnChange()
    {
        $listener = $this->getLocaleUpdateListener(['cookie'], false);
        $listener->onLocaleChange($this->getFilterLocaleSwitchEvent(false));
        $addedListeners = $this->dispatcher->getListeners(KernelEvents::RESPONSE);
        $this->assertSame([], $addedListeners);
    }

    public function testUpdateCookieOnResponse()
    {
        $event = $this->getEvent($this->getRequest());

        $logger = $this->getMockLogger();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('Locale Cookie set to [ es ]');

        $listener = $this->getLocaleUpdateListener([], false, $logger);

        $reflectionClass = new ReflectionClass($listener);
        $property        = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'es');

        $response = $listener->updateCookieOnResponse($event);

        /** @var $cookie Cookie */
        [$cookie] = $response->headers->getCookies();
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

        $listener = $this->getLocaleUpdateListener(['session'], false, $logger);

        $reflectionClass = new ReflectionClass($listener);
        $property        = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'tr');

        $this->assertTrue($listener->updateSession());
    }

    public function testNotUpdateSessionNoGuesser()
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(['cookie']);

        $reflectionClass = new ReflectionClass($listener);
        $property        = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        $this->assertFalse($listener->updateSession());
    }

    public function testNotUpdateSessionSameLocale()
    {
        $this->session->setLocale('el');
        $listener = $this->getLocaleUpdateListener(['session']);

        $reflectionClass = new ReflectionClass($listener);
        $property        = $reflectionClass->getProperty('locale');
        $property->setAccessible(true);
        $property->setValue($listener, 'el');

        $this->assertFalse($listener->updateSession());
    }

    public function testGetSubscribedEvents()
    {
        $subcribedEvents = LocaleUpdateListener::getSubscribedEvents();

        $this->assertEquals(['onLocaleChange'], $subcribedEvents[FilterLocaleSwitchEvent::class]);
    }

    private function getFilterLocaleSwitchEvent($withCookieSet = true, $locale = 'fr')
    {
        return new FilterLocaleSwitchEvent($this->getRequest($withCookieSet), $locale);
    }

    private function getLocaleUpdateListener($registeredGuessers = [], $updateCookie = false, $logger = null)
    {
        $listener = new LocaleUpdateListener(
            $this->dispatcher,
            $this->getLocaleCookie($updateCookie),
            $this->session,
            $registeredGuessers,
            $logger
        );

        return $listener;
    }

    private function getEvent(Request $request)
    {
        return new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST, new Response);
    }


    private function getLocaleCookie($updateCookie)
    {
        $cookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, $updateCookie);

        return $cookie;
    }

    private function getRequest($withCookieSet = false)
    {
        $request = Request::create('/', 'GET', [], $withCookieSet ? ['lunetics_locale' => 'de'] : []);

        return $request;
    }

    private function getMockLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
