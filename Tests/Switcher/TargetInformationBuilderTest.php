<?php

namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\Switcher\TargetInformationBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class TargetInformationBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function locales()
    {
        return array(
            'set 1' => array('/hello-world/', 'de', array('de', 'en', 'fr')),
            'set 2' => array('/', 'de_DE', array('de', 'en', 'fr', 'nl')),
            'set 3' => array('/test/', 'de', array('de', 'fr_FR', 'es_ES', 'nl')),
            'set 4' => array('/foo', 'de', array('de', 'en')),
            'set 5' => array('/foo', 'de', array('de')),
            'set 6' => array('/', 'de_DE', array('de_DE', 'en', 'fr', 'nl'))
        );
    }

    /**
     * @dataProvider locales
     */
    public function testProvideRouteInInformationBuilder($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
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

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales);
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
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
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

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, false, true);
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
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
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

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, false, false);
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
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales);
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
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, false, false);
        if (count($allowedLocales) > 1) {
            $router->expects($this->atLeastOnce())
                    ->method('generate')
                    ->with($this->equalTo($route), $this->arrayHasKey('foo'));

            $targetInformationBuilder->getTargetInformations(null, array('foo' => 'bar'));
        }
    }


    /**
     * @dataProvider locales
     */
    public function testShowCurrentLocale($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, true);
        $targetInformation = $targetInformationBuilder->getTargetInformations();

        $this->assertEquals($locale, $targetInformation['current_locale']);

        $this->assertCount(count($allowedLocales), $targetInformation['locales']);
        foreach ($allowedLocales as $allowed) {
            $this->assertArrayHasKey($allowed, $targetInformation['locales']);
        }
    }

    public function testGenerateNotCalledIfNoRoute()
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->attributes->set('_route', null);
        $router = $this->getRouter();

        $targetInformationBuilder = new TargetInformationBuilder($request, $router, array('de', 'en', 'fr'), true, false);
        $router
            ->expects($this->never())
            ->method('generate')
        ;

        $targetInformationBuilder->getTargetInformations();
    }

    private function getRequestWithBrowserPreferences($route = "/")
    {
        $request = Request::create($route);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRouter()
    {
        return $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->disableOriginalConstructor()->getMock();
    }
}
