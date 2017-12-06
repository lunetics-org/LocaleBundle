<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

use Lunetics\LocaleBundle\Controller\LocaleController;
use Symfony\Component\Routing\RouterInterface;
use Lunetics\LocaleBundle\Validator\MetaValidator;

class LocaleControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testControllerThrowsException()
    {
        $metaValidatorMock = $this->getMetaValidatorMock(false);
        $metaValidatorMock->expects($this->atLeastOnce())
                ->method('isAllowed')
                ->with($this->anything())
                ->will($this->returnCallback(function ($v) {
            return $v === 'de';
        }));

        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('en');
        $localeController = new LocaleController($this->getRouterMock(), $metaValidatorMock);

        $this->setExpectedException('\InvalidArgumentException');
        $localeController->switchAction($request);
    }

    public function testControllerRedirectsToReferrer()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');
        $request->headers->set('referer', 'http://foo');

        $localeController = $this->getLocaleController(true);
        $response = $localeController->switchAction($request);
        $this->assertEquals('http://foo', $response->getTargetUrl());
    }

    public function testControllerRedirectsToFallbackRoute()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');

        $localeController = $this->getLocaleController(true);
        $response = $localeController->switchAction($request);
        $this->assertEquals('http://fallback_route.com/', $response->getTargetUrl());
    }

    public function testControllerRedirectsToFallbackRouteWithParams()
    {
        $request = $this->getRequestWithBrowserPreferences('/?foo=bar');
        $request->setLocale('de');

        $localeController = $this->getLocaleController(true);
        $response = $localeController->switchAction($request);
        $this->assertEquals('http://fallback_route.com/?foo=bar', $response->getTargetUrl());
    }

    public function testControllerNoMatchRedirect()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');

        $localeController = new LocaleController(null, $this->getMetaValidatorMock());
        $response = $localeController->switchAction($request);
        $this->assertEquals('http://localhost/', $response->getTargetUrl());
    }

    public function getLocaleController($useReferrer = true, $redirectToRoute = 'fallback_route', $statuscode = '302')
    {
        $routerMock = $this->getRouterMock();
        $metaValidatorMock = $this->getMetaValidatorMock();

        return new LocaleController($routerMock, $metaValidatorMock, $useReferrer, $redirectToRoute, $statuscode);
    }

    private function getRequestWithBrowserPreferences($route = "/")
    {
        $request = Request::create($route);
        $request->setSession($this->getSessionMock());
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRouterMock()
    {
        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock->expects($this->any())
                ->method('generate')
                ->with($this->equalTo('fallback_route'), $this->anything())
                ->will($this->returnValue('http://fallback_route.com/'));

        return $routerMock;
    }

    private function getMetaValidatorMock($expectTrue = true)
    {
        $metaValidator = $this->createMock(MetaValidator::class);
        if ($expectTrue) {
            $metaValidator->expects($this->atLeastOnce())
                    ->method('isAllowed')
                    ->will($this->returnValue(true));
        }

        return $metaValidator;
    }

    private function getSessionMock()
    {
        return new Session(new MockArraySessionStorage());
    }
}
