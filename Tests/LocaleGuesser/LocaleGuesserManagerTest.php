<?php

namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class LocaleGuesserManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testLocaleGuessingInvalidGuesser()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $guesserManager = new LocaleGuesserManager(array(0 => 'foo'));
        $guesserManager->addGuesser($this->getGuesserMock(), 'bar');
        $guesserManager->runLocaleGuessing($this->getRequestWithoutLocaleQuery());
    }

    public function testLocaleIsIdentifiedByTheRouterGuessingService()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser(true, array('en', 'fr')), 'router');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertEquals('fr', $guessing);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist()
    {
        $request = $this->getRequestWithoutLocaleQuery();
        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser(true, array('fr', 'de', 'en')), 'router');
        $manager->addGuesser($this->getGuesserMock(), 'browser');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertEquals(false, $guessing);
    }

    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create(' / hello - world', 'GET', array('_locale' => $locale));

        return $request;
    }

    private function getRequestWithoutLocaleQuery()
    {
        $request = Request::create(' / hello - world', 'GET');

        return $request;
    }

    /**
     * @return LocaleGuesserInterface
     */
    public function getGuesserMock()
    {
        return $this->getMock('Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface');
    }
}
