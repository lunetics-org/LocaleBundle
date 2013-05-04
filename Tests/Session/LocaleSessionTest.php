<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleInformation;

use Lunetics\LocaleBundle\Session\LocaleSession;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSessionTest extends \PHPUnit_Framework_TestCase
{
    public function testHasLocaleChanged()
    {
        $localeEn = uniqid('en:');
        $localeFr = uniqid('fr:');

        $session = $this->getMockSession();
        $session
            ->expects($this->at(0))
            ->method('get')
            ->with('lunetics_locale')
            ->will($this->returnValue($localeEn));

        $session
            ->expects($this->at(1))
            ->method('get')
            ->with('lunetics_locale')
            ->will($this->returnValue($localeFr));

        $localeSession = new LocaleSession($session);

        $this->assertFalse($localeSession->hasLocaleChanged($localeEn));
        $this->assertTrue($localeSession->hasLocaleChanged($localeEn));
    }

    public function testSetGetLocale()
    {
        $locale = uniqid('locale:');

        $session = $this->getMockSession();

        $session
            ->expects($this->at(0))
            ->method('set')
            ->with('lunetics_locale', $locale)
        ;

        $session
            ->expects($this->at(1))
            ->method('get')
            ->with('lunetics_locale', $locale)
            ->will($this->returnValue($locale))
        ;

        $localeSession = new LocaleSession($session);

        $localeSession->setLocale($locale);
        $this->assertEquals($locale, $localeSession->getLocale($locale));
    }

    public function testGetSessionVar()
    {
        $localeSession = new LocaleSession($this->getMockSession());

        $this->assertEquals('lunetics_locale', $localeSession->getSessionVar());
    }

    public function getMockSession()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Session\Session');
    }
}