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

use Lunetics\LocaleBundle\LocaleGuesser\RouterLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function testLocaleIsIdentifiedByTheRouterGuessingService()
    {
        $request = $this->getRequestWithLocaleQuery('fr');
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->any())
                ->method('isAllowed')
                ->with('fr')
                ->will($this->returnValue(true));

        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');

        $guesserMock = $this->getGuesserMock();
        $guesserMock->expects($this->any())
                ->method('guessLocale')
                ->will($this->returnValue(false));
        $manager->addGuesser($guesserMock, 'browser');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertEquals('fr', $guessing);
    }

    public function testLocaleIsNotIdentifiedIfNoQueryParamsExist()
    {
        $request = $this->getRequestWithoutLocaleQuery();
        $metaValidator = $this->getMetaValidatorMock();

        $metaValidator->expects($this->never())
                ->method('isAllowed');

        $order = array(0 => 'router', 1 => 'browser');
        $manager = new LocaleGuesserManager($order);
        $manager->addGuesser(new RouterLocaleGuesser($metaValidator), 'router');
        $guesserMock = $this->getGuesserMock();
        $guesserMock->expects($this->any())
                ->method('guessLocale')
                ->will($this->returnValue(false));
        $manager->addGuesser($guesserMock, 'browser');
        $guessing = $manager->runLocaleGuessing($request);
        $this->assertFalse($guessing);
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
}
