<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\QueryLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Lunetics\LocaleBundle\Validator\MetaValidator;

class LocaleGuesserManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testLocaleGuessingInvalidGuesser()
    {
        $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        $guesserManager = new LocaleGuesserManager(array(0 => 'foo'));
        $guesserManager->addGuesser($this->getGuesserMock(), 'bar');
        $guesserManager->runLocaleGuessing($this->getRequestWithoutLocaleQuery());
    }

    public function testLocaleIsIdentifiedByTheQueryGuessingService()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->any())
                ->method('isAllowed')
                ->with('fr')
                ->will($this->returnValue(true));

        $logger = $this->getMockLogger();
        $logger
            ->expects($this->at(0))
            ->method('info', array())
            ->with('Locale Query Guessing Service Loaded')
        ;
        $logger
            ->expects($this->at(1))
            ->method('info', array())
            ->with('Locale has been identified by guessing service: ( Query )')
        ;

        $order = array(0 => 'query', 1 => 'router');
        $manager = new LocaleGuesserManager($order, $logger);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($metaValidator), 'query');

        $guesserMock = $this->getGuesserMock();
        $guesserMock->expects($this->any())
                ->method('guessLocale')
                ->will($this->returnValue(false));
        $manager->addGuesser($guesserMock, 'browser');
        $locale = $manager->runLocaleGuessing($request);
        $this->assertEquals('fr', $locale);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist()
    {
        $request = $this->getRequestWithoutLocaleQuery();
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->never())
                ->method('isAllowed');

        $order = array(0 => 'query', 1 => 'router');
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');
        $manager->addGuesser(new QueryLocaleGuesser($metaValidator), 'query');
        $guesserMock = $this->getGuesserMock();
        $guesserMock->expects($this->any())
                ->method('guessLocale')
                ->will($this->returnValue(false));
        $manager->addGuesser($guesserMock, 'browser');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertFalse($guessing);
    }

    public function testGetPreferredLocales()
    {
        $manager = new LocaleGuesserManager(array());
        $value = uniqid('preferredLocales:');

        $reflectionsClass = new \ReflectionClass(get_class($manager));
        $property = $reflectionsClass->getProperty('preferredLocales');
        $property->setAccessible(true);
        $property->setValue($manager, $value);

        $this->assertEquals($value, $manager->getPreferredLocales());
    }

    public function testGetGuessingOrder()
    {
        $order = array(0 => 'query', 1 => 'router');

        $manager = new LocaleGuesserManager($order);

        $this->assertEquals($order, $manager->getGuessingOrder());
    }

    public function testRemoveGuesser()
    {
        $order = array(0 => 'query', 1 => 'router');
        $manager = new LocaleGuesserManager($order);

        $manager->addGuesser($this->getGuesserMock(), 'mock');

        $manager->removeGuesser('mock');
        $this->assertNull($manager->getGuesser('mock'));
    }

    private function getRequestWithLocaleQuery($locale = 'en')
    {
        $request = Request::create(' / hello - world', 'GET');
        $request->query->set('_locale', $locale);

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

    private function getMockLogger()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
    }

}
