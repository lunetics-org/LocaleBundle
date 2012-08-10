<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class LocaleGuesserManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserNameIsMappedToService()
    {
        $routerGuesser = new RouterLocaleGuesser();
        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order, $routerGuesser);
        $guessersMap = $manager->getGuessingServices();
        $this->assertTrue($guessersMap['router'] instanceof LocaleGuesserInterface);
    }

    public function testLocaleIsIdentifiedByTheRouterGuessingService()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $routerGuesser = new RouterLocaleGuesser();
        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order, $routerGuesser);
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertEquals('fr', $guessing);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist()
    {
        $request = $this->getRequestWithoutLocaleQuery();
        $routerGuesser = new RouterLocaleGuesser();
        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order, $routerGuesser);
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertEquals(false, $guessing);
    }

    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create('/hello-world', 'GET', array('_locale' => $locale));

        return $request;
    }

    private function getRequestWithoutLocaleQuery()
    {
        $request = Request::create('/hello-world', 'GET');

        return $request;
    }
}
