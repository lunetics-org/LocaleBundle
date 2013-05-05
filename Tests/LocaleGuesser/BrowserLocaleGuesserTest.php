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

use Lunetics\LocaleBundle\LocaleGuesser\BrowserLocaleGuesser;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserInterface;
use Symfony\Component\HttpFoundation\Request;

class BrowserLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuesserExtendsInterface()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = $this->getGuesser($metaValidator);
        $this->assertTrue($guesser instanceof LocaleGuesserInterface);
    }

    public function testNoPreferredLocale()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $guesser = $this->getGuesser($metaValidator);
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsIdentifiedFromBrowser()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->will($this->returnValue(true));

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }


    public function testLocaleIsIdentifiedFromBrowserTestFallbackForNoIntlExtension()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator->expects($this->once())
                ->method('isAllowed')
                ->with('fr_FR')
                ->will($this->returnValue(true));

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals('fr_FR', $guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguage()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator->expects($this->any())
                ->method('isAllowed')
                ->will($this->returnValue(false));
        $this->assertFalse($guesser->guessLocale($request));
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguageTestFallbackForNoIntlExtension()
    {
        $metaValidator = $this->getMetaValidatorMock();
        $request = $this->getRequestWithBrowserPreferences();
        $guesser = $this->getGuesser($metaValidator);

        $metaValidator->expects($this->any())
                ->method('isAllowed')
                ->will($this->returnValue(false));

        $guesser->guessLocale($request);
        $this->assertFalse($guesser->getIdentifiedLocale());
    }

    public function correctLocales()
    {
        return array(
            'strict 1' => array(array('en', 'de', 'fr'), 'fr', true),
            'unstrict 1' => array(array('en', 'de', 'fr'), 'fr_CH', false),
            'strict 2' => array(array('de', 'en_GB', 'fr', 'fr_FR'), 'fr', true),
            'unstrict 2' => array(array('de', 'en_GB', 'fr', 'fr_FR'), 'fr_CH', false),
            'strict 3' => array(array('en', 'en_GB', 'fr', 'fr'), 'fr', true),
            'unstrict 3' => array(array('en', 'en_GB', 'fr', 'fr_CH'), 'fr_CH', false),
            'strict 4' => array(array('fr', 'en_GB'), 'fr', true),
            'unstrict 4' => array(array('fr', 'en_GB'), 'fr_CH', false),
            'strict 5' => array(array('fr_LI', 'en'), 'en', true),
            'unstrict 5' => array(array('fr_LI', 'en'), 'en_GB', false)
        );
    }

    /**
     * @dataProvider correctLocales
     *
     * @param array $allowedLocales
     * @param string $result
     * @param bool $strict
     */
    public function testEnsureCorrectLocaleForAllowedLocales($allowedLocales, $result, $strict)
    {
        $metaValidator = $this->getMetaValidatorMock();
        $request = $this->getRequestWithBrowserPreferencesMultipleLangLocales();
        $guesser = $this->getGuesser($metaValidator, $allowedLocales);

        // Emulate a simple validator for strict mode
        $metaValidator->expects($this->atLeastOnce())
                ->method('isAllowed')
                ->will($this->returnCallback(function ($v) use ($allowedLocales, $strict) {
            if (in_array($v, $allowedLocales)) {
                return true;
            } elseif (!$strict) {
                $splittedLocale = explode('_', $v);
                $v = count($splittedLocale) > 1 ? $splittedLocale[0] : $v;

                return in_array($v, $allowedLocales);
            }

            return false;
        }));

        $this->assertTrue($guesser->guessLocale($request));
        $this->assertEquals($result, $guesser->getIdentifiedLocale());
    }

    private function getGuesser($metaValidator, $allowedLocales = array('en', 'fr', 'de'))
    {
        $guesser = new BrowserLocaleGuesser($metaValidator, $allowedLocales);

        return $guesser;
    }

    private function getRequestWithBrowserPreferences()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithBrowserPreferencesMultipleLangLocales()
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-CH,fr;q=0.8,fr-FR;q=0.7, en-GB;q=0.6,en;q=0.5');

        return $request;
    }

    private function getMetaValidatorMock()
    {
        $mock = $this->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
