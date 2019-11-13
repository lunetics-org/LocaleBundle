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

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\EventListener\LocaleListener;
use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Matcher\DefaultBestLocaleMatcher;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use function in_array;

class LocaleListenerTest extends TestCase
{
    /** @var MockObject|Response */
    private $response;

    /** @var MockObject|LoggerInterface */
    private $logger;

    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var LocaleGuesserManager|MockObject */
    private $localeGuesserManager;

    protected function setUp() : void
    {
        $this->response             = $this->createMock(Response::class);
        $this->logger               = $this->createMock(LoggerInterface::class);
        $this->validator            = $this->createMock(MetaValidator::class);
        $this->localeGuesserManager = $this->createMock(LocaleGuesserManager::class);
    }

    public function testDefaultLocaleWithoutParams() : void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager());
        $request  = Request::create('/');
        $request->headers->set('Accept-language', '');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }

    public function getTestDataForBestLocaleMatcher() : array
    {
        return [
            ['fr', ['fr'], 'fr', 'en'],
            ['fr_FR', ['fr'], 'fr', 'en'],
            ['fr_FR', ['fr_FR'], 'fr_FR', 'en'],
            ['fr_FR', ['en_GB'], 'en', 'en'],
        ];
    }

    /**
     * @dataProvider getTestDataForBestLocaleMatcher
     */
    public function testAllowedLocaleWithMatcher($browserLocale, $allowedlocales, $expectedLocale, $fallback) : void
    {
        $listener = $this->getListener($fallback, $this->getGuesserManager(), null, $this->getBestLocaleMatcher($allowedlocales));
        $request  = Request::create('/');
        $request->headers->set('Accept-language', $browserLocale);
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals($expectedLocale, $request->getLocale());
    }

    public function testCustomLocaleIsSetWhenParamsExist() : void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager());
        $request  = Request::create('/', 'GET');
        $request->attributes->set('_locale', 'de');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals('de', $request->getLocale());
        $this->assertEquals('de', $request->attributes->get('_locale'));
    }

    public function testCustomLocaleIsSetWhenQueryExist() : void
    {
        $listener = $this->getListener('fr', $this->getGuesserManager([0 => 'router', 1 => 'query', 2 => 'browser']));
        $request  = Request::create('/', 'GET');
        $request->query->set('_locale', 'de');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals('de', $request->getLocale());
        $this->assertEquals('de', $request->attributes->get('_locale'));
    }

    /**
     * Router is prio 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testRouteLocaleIsReturnedIfRouterIsPrio1() : void
    {
        $request  = $this->getFullRequest();
        $manager  = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('es', $request->getLocale());
        $this->assertEquals('es', $request->attributes->get('_locale'));
    }

    /**
     * Browser is prio 1
     * Request contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserLocaleIsReturnedIfBrowserIsPrio1() : void
    {
        $request  = $this->getFullRequest();
        $manager  = $this->getGuesserManager([1 => 'browser', 2 => 'router']);
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
        $this->assertEquals('fr_FR', $request->attributes->get('_locale'));
    }

    /**
     * Router is prio 1
     * Request DOES NOT contains _locale parameter in router
     * Request contains browser locale preferences
     */
    public function testBrowserTakeOverIfRouterParamsFail() : void
    {
        $request  = $this->getFullRequest(null);
        $manager  = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
        $this->assertEquals('fr_FR', $request->attributes->get('_locale'));
    }

    public function testThatGuesserIsNotCalledIfNotInGuessingOrder() : void
    {
        $request  = $this->getRequestWithRouterParam();
        $manager  = $this->getGuesserManager([0 => 'browser']);
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('en', $request->getLocale());
    }

    public function testDispatcherIsFired() : void
    {
        $dispatcherMock = $this->createMock(EventDispatcher::class);
        $dispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FilterLocaleSwitchEvent::class));

        $listener = $this->getListener('fr', $this->getGuesserManager());
        $listener->setEventDispatcher($dispatcherMock);


        $event = $this->getEvent($this->getRequestWithRouterParam());
        $listener->onKernelRequest($event);
    }

    public function testDispatcherIsNotFired() : void
    {
        $dispatcherMock = $this->createMock(EventDispatcher::class);
        $dispatcherMock->expects($this->never())
            ->method('dispatch');

        $manager = $this->getGuesserManager();
        $manager->removeGuesser('session');
        $manager->removeGuesser('cookie');
        $listener = $this->getListener('fr', $manager);
        $listener->setEventDispatcher($dispatcherMock);

        $event = $this->getEvent($this->getRequestWithRouterParam());
        $listener->onKernelRequest($event);
    }

    /**
     * Request with empty route params and empty browser preferences
     */
    public function testDefaultLocaleIfEmptyRequest() : void
    {
        $request  = $this->getEmptyRequest();
        $manager  = $this->getGuesserManager();
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('en', $request->getLocale());
    }

    public function testAjaxRequestsAreHandled() : void
    {
        $request = $this->getRequestWithRouterParam('fr');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $manager  = $this->getGuesserManager([0 => 'router']);
        $listener = $this->getListener('en', $manager);
        $event    = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }

    public function testOnLocaleDetectedSetVaryHeader() : void
    {
        $listener = $this->getListener();

        $this->response
            ->expects($this->once())
            ->method('setVary')
            ->with('Accept-Language')
            ->willReturn($this->response);

        $filterResponseEvent = $this->getMockFilterResponseEvent();
        $filterResponseEvent
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $listener->onLocaleDetectedSetVaryHeader($filterResponseEvent);
    }

    public function testOnLocaleDetectedDisabledVaryHeader() : void
    {
        $listener = $this->getListener();
        $listener->setDisableVaryHeader(true);

        $this->response
            ->expects($this->never())
            ->method('setVary');

        $filterResponseEvent = $this->getMockFilterResponseEvent();
        $filterResponseEvent
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);

        $listener->onLocaleDetectedSetVaryHeader($filterResponseEvent);
    }

    public function excludedPatternDataProvider() : array
    {
        return [
            [null, true],
            ['.*', false],
            ['/api$', true],
            ['^/api', false],
        ];
    }

    /**
     * @dataProvider excludedPatternDataProvider
     */
    public function testRunLocaleGuessingIsNotFiredIfPatternMatches($pattern, $called) : void
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/users']);

        $this->localeGuesserManager
            ->expects($this->exactly((int) $called))
            ->method('runLocaleGuessing');

        $listener = $this->getListener('en', $this->localeGuesserManager);
        $listener->setExcludedPattern($pattern);

        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
    }

    public function testLogEvent() : void
    {
        $message = 'Setting [ 1 ] as locale for the (Sub-)Request';

        $request = $this->getEmptyRequest();

        $this->localeGuesserManager
            ->expects($this->once())
            ->method('runLocaleGuessing')
            ->with($request)
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with($message, []);

        $listener = $this->getListener('en', $this->localeGuesserManager, $this->logger);

        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
    }

    public function testGetSubscribedEvents() : void
    {
        $subscribedEvents = LocaleListener::getSubscribedEvents();

        $this->assertEquals([['onKernelRequest', 24]], $subscribedEvents[KernelEvents::REQUEST]);
        $this->assertEquals(['onLocaleDetectedSetVaryHeader'], $subscribedEvents[KernelEvents::RESPONSE]);
    }

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function getListener($locale = 'en', $manager = null, $logger = null, $matcher = null) : LocaleListener
    {
        if (null === $manager) {
            $manager = $this->getGuesserManager();
        }

        $listener = new LocaleListener($locale, $manager, $matcher, $logger);
        $listener->setEventDispatcher(new EventDispatcher());

        return $listener;
    }

    private function getBestLocaleMatcher(array $allowedLocales) : DefaultBestLocaleMatcher
    {
        return new DefaultBestLocaleMatcher(new AllowedLocalesProvider($allowedLocales));
    }

    private function getGuesserManager($order = [1 => 'router', 2 => 'browser']) : LocaleGuesserManager
    {
        $allowedLocales = ['de', 'fr', 'fr_FR', 'nl', 'es', 'en'];

        $this->validator
            ->method('isAllowed')
            ->willReturnCallback(
                function ($v) use ($allowedLocales) {
                    return in_array($v, $allowedLocales, true);
                }
            );

        $manager        = new LocaleGuesserManager($order);
        $routerGuesser  = new RouterLocaleGuesser($this->validator);
        $browserGuesser = new BrowserLocaleGuesser($this->validator);
        $cookieGuesser  = new CookieLocaleGuesser($this->validator, 'lunetics_locale');
        $queryGuesser   = new QueryLocaleGuesser($this->validator, '_locale');
        $manager->addGuesser($queryGuesser, 'query');
        $manager->addGuesser($routerGuesser, 'router');
        $manager->addGuesser($browserGuesser, 'browser');
        $manager->addGuesser($cookieGuesser, 'cookie');

        return $manager;
    }

    private function getRequestWithRouterParam($routerLocale = 'es') : Request
    {
        $request = Request::create('/');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        if (! empty($routerLocale)) {
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', '');

        return $request;
    }

    private function getFullRequest($routerLocale = 'es') : Request
    {
        $request = Request::create('/');
        if (! empty($routerLocale)) {
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getEmptyRequest() : Request
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');

        return $request;
    }

    /**
     * @return MockObject|FilterResponseEvent
     */
    private function getMockFilterResponseEvent()
    {
        return $this->createMock(FilterResponseEvent::class);
    }
}
