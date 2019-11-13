<?php

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\Switcher;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use function count;

class TargetInformationBuilderTest extends TestCase
{
    /** @var MockObject|RouterInterface */
    private $router;

    protected function setUp() : void
    {
        $this->router = $this->createMock(RouterInterface::class);
    }

    public function locales() : array
    {
        return [
            'set 1' => ['/hello-world/', 'de', ['de', 'en', 'fr']],
            'set 2' => ['/', 'de_DE', ['de', 'en', 'fr', 'nl']],
            'set 3' => ['/test/', 'de', ['de', 'fr_FR', 'es_ES', 'nl']],
            'set 4' => ['/foo', 'de', ['de', 'en']],
            'set 5' => ['/foo', 'de', ['de']],
            'set 6' => ['/', 'de_DE', ['de_DE', 'en', 'fr', 'nl']],
        ];
    }

    /**
     * @dataProvider locales
     */
    public function testProvideRouteInInformationBuilder(string $route, string $locale, array $allowedLocales) : void
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $count   = count($allowedLocales) - 1;
        if ($count >= 1) {
            $this->router
                ->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo('route_foo'), $this->anything())
                ->willReturn($route . '_generated');
        } else {
            $this->router
                ->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($request, $this->router, new AllowedLocalesProvider($allowedLocales));
        $targetInformation        = $targetInformationBuilder->getTargetInformations('route_foo');

        $this->assertEquals($route, $targetInformation['current_route']);
        foreach ($allowedLocales as $check) {
            if (0 !== strpos($locale, $check)) {
                $this->assertEquals($route . '_generated', $targetInformation['locales'][$check]['link']);
            }
        }
    }

    /**
     * @dataProvider locales
     */
    public function testNotProvideRouteInInformationBuilder(string $route, string $locale, array $allowedLocales) : void
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $count   = count($allowedLocales) - 1;
        if ($count >= 1) {
            $this->router
                ->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo('lunetics_locale_switcher'), $this->anything())
                ->willReturn($route . '_generated');
        } else {
            $this->router->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($request, $this->router, new AllowedLocalesProvider($allowedLocales), false, true);
        $targetInformation        = $targetInformationBuilder->getTargetInformations();

        $this->assertEquals($route, $targetInformation['current_route']);
        foreach ($allowedLocales as $check) {
            if (0 !== strpos($locale, $check)) {
                $this->assertEquals($route . '_generated', $targetInformation['locales'][$check]['link']);
            }
        }
    }

    /**
     * @dataProvider locales
     */
    public function testNotProvideRouteInInformationBuilderNoRouter(string $route, string $locale, array $allowedLocales) : void
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $count        = count($allowedLocales) - 1;
        if ($count >= 1) {
            $this->router
                ->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo($route), $this->anything())
                ->willReturn($route . '_generated');
        } else {
            $this->router
                ->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $this->router, new AllowedLocalesProvider($allowedLocales), false, false);
        $targetInformation        = $targetInformationBuilder->getTargetInformations();

        $this->assertEquals($route, $targetInformation['current_route']);
        foreach ($allowedLocales as $check) {
            if (0 !== strpos($locale, $check)) {
                $this->assertEquals($route . '_generated', $targetInformation['locales'][$check]['link']);
            }
        }
    }

    /**
     * @dataProvider locales
     *
     */
    public function testInformationBuilder(string $route, string $locale, array $allowedLocales) : void
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $this->router, new AllowedLocalesProvider($allowedLocales));
        $targetInformation        = $targetInformationBuilder->getTargetInformations();
        $this->assertEquals($locale, $targetInformation['current_locale']);
        $count = count($allowedLocales) - 1;
        if ($count >= 1) {
            $this->assertCount($count, $targetInformation['locales']);
        } else {
            $this->assertCount(0, $targetInformation['locales']);
        }
    }

    /**
     * @dataProvider locales
     */
    public function testInformationBuilderWithParams(string $route, string $locale, array $allowedLocales) : void
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);

        $targetInformationBuilder = new TargetInformationBuilder($request, $this->router, new AllowedLocalesProvider($allowedLocales), false, false);

        $this->router
            ->expects($this->exactly(count($allowedLocales) - 1))
            ->method('generate')
            ->with($this->equalTo($route), $this->arrayHasKey('foo'));

        $targetInformationBuilder->getTargetInformations(null, ['foo' => 'bar']);

    }

    /**
     * @dataProvider locales
     */
    public function testShowCurrentLocale(string $route, string $locale, array $allowedLocales) : void
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $this->router, new AllowedLocalesProvider($allowedLocales), true);
        $targetInformation        = $targetInformationBuilder->getTargetInformations();

        $this->assertEquals($locale, $targetInformation['current_locale']);

        $this->assertCount(count($allowedLocales), $targetInformation['locales']);
        foreach ($allowedLocales as $allowed) {
            $this->assertArrayHasKey($allowed, $targetInformation['locales']);
        }
    }

    public function testGenerateNotCalledIfNoRoute() : void
    {
        $requestStack = $this->getRequestWithBrowserPreferences('/', '', ['_route' => null]);

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $this->router, new AllowedLocalesProvider(['de', 'en', 'fr']), true, false);
        $this->router
            ->expects($this->never())
            ->method('generate');

        $targetInformationBuilder->getTargetInformations();
    }

    private function getRequestWithBrowserPreferences(string $route, string $locale, array $attributes) : RequestStack
    {
        $request      = Request::create($route, Request::METHOD_GET, $attributes);
        $requestStack = new RequestStack();
        $request->setLocale($locale);
        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');
        $requestStack->push($request);

        return $requestStack;
    }
}
