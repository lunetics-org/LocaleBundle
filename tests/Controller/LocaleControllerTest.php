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

namespace Lunetics\LocaleBundle\Tests\Controller;

use InvalidArgumentException;
use Lunetics\LocaleBundle\Controller\LocaleController;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;

class LocaleControllerTest extends TestCase
{
    /** @var MockObject|RouterInterface */
    private $router;

    /** @var MetaValidator|MockObject */
    private $validator;


    public function setUp() : void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->router
            ->expects($this->any())
            ->method('generate')
            ->with($this->equalTo('fallback_route'), $this->anything())
            ->willReturn('http://fallback_route.com/');

        $this->validator = $this->createMock(MetaValidator::class);

    }

    public function testControllerThrowsException() : void
    {
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->with($this->anything())
            ->willReturnCallback(
                function ($v) {
                    return $v === 'de';
                }
            );

        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('en');
        $localeController = new LocaleController($this->router, $this->validator);

        $this->expectException(InvalidArgumentException::class);
        $localeController->switchAction($request);
    }

    public function testControllerRedirectsToReferrer() : void
    {
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturn(true);

        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');
        $request->headers->set('referer', 'http://foo');

        $localeController = $this->getLocaleController(true);
        $response         = $localeController->switchAction($request);
        $this->assertEquals('http://foo', $response->getTargetUrl());
    }

    public function testControllerRedirectsToFallbackRoute() : void
    {
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturn(true);

        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');

        $localeController = $this->getLocaleController(true);
        $response         = $localeController->switchAction($request);
        $this->assertEquals('http://fallback_route.com/', $response->getTargetUrl());
    }

    public function testControllerRedirectsToFallbackRouteWithParams() : void
    {
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturn(true);

        $request = $this->getRequestWithBrowserPreferences('/?foo=bar');
        $request->setLocale('de');

        $localeController = $this->getLocaleController(true);
        $response         = $localeController->switchAction($request);
        $this->assertEquals('http://fallback_route.com/?foo=bar', $response->getTargetUrl());
    }

    public function testControllerNoMatchRedirect() : void
    {
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturn(true);

        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale('de');

        $localeController = new LocaleController(null, $this->validator);
        $response         = $localeController->switchAction($request);
        $this->assertEquals('http://localhost/', $response->getTargetUrl());
    }

    public function getLocaleController(bool $useReferrer = true, string $redirectToRoute = 'fallback_route', string $statuscode = '302') : LocaleController
    {
        return new LocaleController($this->router, $this->validator, $useReferrer, $redirectToRoute, $statuscode);
    }

    private function getRequestWithBrowserPreferences(string $route = '/') : Request
    {
        $request = Request::create($route);
        $request->setSession($this->getSessionMock());
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getSessionMock() : Session
    {
        return new Session(new MockArraySessionStorage());
    }
}
