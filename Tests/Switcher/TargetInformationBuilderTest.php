<?php

namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TargetInformationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function locales()
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
    public function testProvideRouteInInformationBuilder($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route', $route]);
        $router = $this->getRouter();
        $count = count($allowedLocales) - 1;
        if ($count >= 1) {
            $router->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo('route_foo'), $this->anything())
                ->will($this->returnValue($route . '_generated'));
        } else {
            $router->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, new AllowedLocalesProvider($allowedLocales));
        $targetInformation = $targetInformationBuilder->getTargetInformations('route_foo');

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
    public function testNotProvideRouteInInformationBuilder($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();
        $count = count($allowedLocales) - 1;
        if ($count >= 1) {
            $router->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo('lunetics_locale_switcher'), $this->anything())
                ->will($this->returnValue($route . '_generated'));
        } else {
            $router->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, new AllowedLocalesProvider($allowedLocales), false, true);
        $targetInformation = $targetInformationBuilder->getTargetInformations();

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
    public function testNotProvideRouteInInformationBuilderNoRouter($route, $locale, $allowedLocales)
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();
        $count = count($allowedLocales) - 1;
        if ($count >= 1) {
            $router->expects($this->exactly($count))
                ->method('generate')
                ->with($this->equalTo($route), $this->anything())
                ->will($this->returnValue($route . '_generated'));
        } else {
            $router->expects($this->never())
                ->method('generate');
        }

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $router, new AllowedLocalesProvider($allowedLocales), false, false);
        $targetInformation = $targetInformationBuilder->getTargetInformations();

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
    public function testInformationBuilder($route, $locale, $allowedLocales)
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $router, new AllowedLocalesProvider($allowedLocales));
        $targetInformation = $targetInformationBuilder->getTargetInformations();
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
     *
     */
    public function testInformationBuilderWithParams($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, new AllowedLocalesProvider($allowedLocales), false, false);
        if (count($allowedLocales) > 1) {
            $router->expects($this->atLeastOnce())
                ->method('generate')
                ->with($this->equalTo($route), $this->arrayHasKey('foo'));

            $targetInformationBuilder->getTargetInformations(null, ['foo' => 'bar']);
        }
    }

    /**
     * @dataProvider locales
     */
    public function testShowCurrentLocale($route, $locale, $allowedLocales)
    {
        $requestStack = $this->getRequestWithBrowserPreferences($route, $locale, ['_route' => $route]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $router, new AllowedLocalesProvider($allowedLocales), true);
        $targetInformation = $targetInformationBuilder->getTargetInformations();

        $this->assertEquals($locale, $targetInformation['current_locale']);

        $this->assertCount(count($allowedLocales), $targetInformation['locales']);
        foreach ($allowedLocales as $allowed) {
            $this->assertArrayHasKey($allowed, $targetInformation['locales']);
        }
    }

    public function testGenerateNotCalledIfNoRoute()
    {
        $requestStack = $this->getRequestWithBrowserPreferences('/', '', ['_route' => null]);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($requestStack, $router, new AllowedLocalesProvider(['de', 'en', 'fr']), true, false);
        $router
            ->expects($this->never())
            ->method('generate');

        $targetInformationBuilder->getTargetInformations();
    }

    /**
     * @param string $route
     * @param string $locale
     *
     * @param array $attributes
     *
     * @return RequestStack
     */
    private function getRequestWithBrowserPreferences($route = "/", $locale = '', $attributes = [])
    {
        $request = Request::create($route);
        $requestStack = new RequestStack();
        $request->setLocale($locale);
        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');
        $requestStack->push($request);

        return $requestStack;
    }

    private function getRouter()
    {
        return $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->disableOriginalConstructor()->getMock();
    }
}
