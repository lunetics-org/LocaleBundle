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

use Lunetics\LocaleBundle\EventListener\LocaleListener;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\CookieLocaleGuesser;
use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultLocaleWithoutParams()
    {
        $listener = new LocaleListener('fr', $this->getGuesserManager(), $this->getLocaleCookie());
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        $event = $this->getEvent($request);

        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }

    public function testCustomLocaleIsSetWhenParamsExist()
    {
        $listener = new LocaleListener('fr', $this->getGuesserManager(), $this->getLocaleCookie());
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
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
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
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
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
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr_FR', $request->getLocale());
    }

    /**
     * Router is prio 1
     * Request DOES NOT contains _locale parameter in rou$defaultLocale = 'en';ter
     * Request contains browser locale preferences
     */
    public function testThatGuesserIsNotCalledIfNotInGuessingOrder()
    {
        $request = $this->getRequestWithRouterParam();
        $manager = $this->getGuesserManager(array(0 => 'browser'));
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
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
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('en', $request->getLocale());
    }

    public function testAjaxRequestsAreHandled()
    {
        $request = $this->getRequestWithRouterParam('fr');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $manager = $this->getGuesserManager(array(0 => 'router'));
        $listener = new LocaleListener('en', $manager, $this->getLocaleCookie());
        $event = $this->getEvent($request);
        $listener->onKernelRequest($event);
        $this->assertEquals('fr', $request->getLocale());
    }

    private function getEvent(Request $request)
    {
        return new GetResponseEvent($this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'), $request, HttpKernelInterface::MASTER_REQUEST);
    }

    private function getGuesserManager($order = array(1 => 'router', 2 => 'browser'))
    {
        $allowedLocales = array('de', 'fr', 'fr_FR', 'nl', 'es', 'en');
        $metaValidator = $this->getMetaValidatorMock();
        $callBack = function ($v) use ($allowedLocales) {
            return in_array($v, $allowedLocales);
        };
        $metaValidator->expects($this->any())
                ->method('isAllowed')
                ->will($this->returnCallback($callBack));

        $manager = new LocaleGuesserManager($order);
        $routerGuesser = new RouterLocaleGuesser($metaValidator, true);
        $browserGuesser = new BrowserLocaleGuesser($metaValidator);
        $cookieGuesser = new CookieLocaleGuesser($metaValidator, 'lunetics_locale');
        $manager->addGuesser($routerGuesser, 'router');
        $manager->addGuesser($browserGuesser, 'browser');
        $manager->addGuesser($cookieGuesser, 'cookie');

        return $manager;
    }

    /**
     * @return LocaleGuesserInterface
     */
    private function getGuesserMock()
    {
        $mock = $this->getMockBuilder('Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface')->disableOriginalConstructor()->getMock();

        return $mock;
    }

    /**
     * @return MetaValidator
     */
    private function getMetaValidatorMock()
    {
        $mock = $this->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')->disableOriginalConstructor()->getMock();

        return $mock;
    }

    private function getRequestWithRouterParam($routerLocale = 'es')
    {
        $request = Request::create('/');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        if (!empty($routerLocale)) {
            $request->attributes->set('_locale', $routerLocale);
        }
        $request->headers->set('Accept-language', '');

        return $request;
    }

    private function getFullRequest($routerLocale = 'es')
    {
        $request = Request::create('/');
        if (!empty($routerLocale)) {
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

    private function getLocaleCookie($onDetection = false, $onSwitch = true)
    {
        $cookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, $onDetection, $onSwitch);

        return $cookie;
    }
}
