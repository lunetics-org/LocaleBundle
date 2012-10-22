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
        $router = $this->getRoute();

        $targetInformationBuilder = new TargetInformationBuilder($route . 'hello');
        $targetInformation = $targetInformationBuilder->getTargetInformations(
            $request,
            $router,
            $allowedLocales
        );
        $this->assertEquals($route . 'hello', $targetInformation['current_route']);
    }

    /**
     * @dataProvider locales
     */
    public function testNotProvideRouteInInformationBuilder($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences($route);
        $request->setLocale($locale);
        $request->attributes->set('_route', $route . 'hello');
        $router = $this->getRoute();

        $targetInformationBuilder = new TargetInformationBuilder();
        $targetInformation = $targetInformationBuilder->getTargetInformations(
            $request,
            $router,
            $allowedLocales
        );
        $this->assertEquals($route . 'hello', $targetInformation['current_route']);
    }

    /**
     * @dataProvider locales
     */
    public function testInformationBuilder($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale($locale);
        $router = $this->getRoute();

        $targetInformationBuilder = new TargetInformationBuilder($route);
        $targetInformation = $targetInformationBuilder->getTargetInformations(
            $request,
            $router,
            $allowedLocales
        );
        $this->assertEquals($locale, $targetInformation['current_locale']);
        if (count($allowedLocales) > 1) {
            $this->assertCount(count($allowedLocales) - 1, $targetInformation['locales']);
            $this->assertArrayNotHasKey($locale, $targetInformation['locales']);
        } else {
            $this->assertArrayNotHasKey('locales', $targetInformation);
        }
    }

    /**
     * @dataProvider locales
     */
    public function testShowCurrentLocale($route, $locale, $allowedLocales)
    {
        $request = $this->getRequestWithBrowserPreferences();
        $request->setLocale($locale);
        $router = $this->getRoute();

        $targetInformationBuilder = new TargetInformationBuilder($route);
        $targetInformation = $targetInformationBuilder->getTargetInformations(
            $request,
            $router,
            $allowedLocales,
            true
        );
        $this->assertCount(count($allowedLocales), $targetInformation['locales']);
        foreach ($allowedLocales as $allowed) {
            $this->assertArrayHasKey($allowed, $targetInformation['locales']);
        }
    }

    private function getRequestWithBrowserPreferences($route = "/")
    {
        $request = Request::create($route);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRoute()
    {
        return $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->disableOriginalConstructor()->getMock();
    }
}
