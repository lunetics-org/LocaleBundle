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

use Lunetics\LocaleBundle\LocaleGuesser\SessionLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserExtendsInterface()
    {
        $request = $this->getRequestWithSessionLocale();
        $guesser = $this->getGuesser($request->getSession(), $this->getMetaValidatorMock());
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testGuessLocaleWithoutSessionVariable()
    {
        $request = $this->getRequestWithSessionLocale();

        $guesser = $this->getGuesser();

        $this->assertFalse($guesser->guessLocale($request));
    }

    public function testLocaleIsRetrievedFromSessionIfSet()
    {
        $request = $this->getRequestWithSessionLocale();
        $metaValidator = $this->getMetaValidatorMock();
        $inputs = array('ru');
        $outputs = array(true);
        $expectation = $metaValidator->expects($this->once())
                ->method('isAllowed');
        $this->setMultipleMatching($expectation, $inputs, $outputs);

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guessLocale($request);
        $this->assertEquals('ru', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotRetrievedFromSessionIfInvalid()
    {
        $request = $this->getRequestWithSessionLocale();
        $metaValidator = $this->getMetaValidatorMock();
        $expectation = $metaValidator->expects($this->once())
                ->method('isAllowed');
        $this->setMultipleMatching($expectation, array('ru'), array(false));

        $guesser = $this->getGuesser($request->getSession(), $metaValidator);
        $guesser->guessLocale($request);
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testSetSessionLocale()
    {
        $locale = uniqid('locale:');

        $guesser = $this->getGuesser();
        $guesser->setSessionLocale($locale, true);

        $this->assertAttributeContains($locale, 'session', $guesser);
    }

    private function getGuesser($session = null, $metaValidator = null)
    {
        if (null === $session) {
            $session = $this->getSession();
        }

        if (null === $metaValidator) {
            $metaValidator = $this->getMetaValidatorMock();
        }

        return new SessionLocaleGuesser($session, $metaValidator);
    }

    private function getRequestWithSessionLocale($locale = 'ru')
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('lunetics_locale', $locale);
        $request = Request::create('/');
        $request->setSession($session);
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getSession()
    {
        return new Session(new MockArraySessionStorage());
    }

    public function getMetaValidatorMock()
    {
        $mock = $this->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')->disableOriginalConstructor()->getMock();

        return $mock;
    }

    /**
     * A callback is built and linked to the mocked method.
     */
    public function setMultipleMatching($expectation,
                                        array $inputs,
                                        array $outputs)
    {
        $testCase = $this;
        $callback = function () use ($inputs, $outputs, $testCase) {
            $args = func_get_args();
            $testCase->assertContains($args[0], $inputs);
            $index = array_search($args[0], $inputs);

            return $outputs[$index];
        };
        $expectation->will($this->returnCallback($callback));
    }
}
