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
use Lunetics\LocaleBundle\Tests\Validator\BaseMetaValidator;
use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;

/**
 * Test for the LocaleInformation
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleInformationTest extends BaseMetaValidator
{
    protected $allowedLocales = array('en', 'de', 'fr_CH');

    public function testGetAllowedLocalesFromConfiguration()
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock(), $this->allowedLocales);
        $this->assertSame($this->allowedLocales, $information->getAllowedLocalesFromConfiguration());
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocales($intlExtension)
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();

        $this->assertContains('en_GB', $foundLocales);
        $this->assertContains('en_US', $foundLocales);
        $this->assertContains('de_CH', $foundLocales);
        $this->assertContains('de_AT', $foundLocales);
        $this->assertContains('fr_CH', $foundLocales);
        $this->assertContains('de', $foundLocales);
        $this->assertContains('en', $foundLocales);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocalesStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension, true);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();
        $this->assertNotContains('en_US', $foundLocales);
        $this->assertNotContains('de_AT', $foundLocales);
        $this->assertContains('de', $foundLocales);
        $this->assertContains('en', $foundLocales);
        $this->assertContains('fr_CH', $foundLocales);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocalesLanguageIdenticalToRegion($intlExtension)
    {
        $this->markTestSkipped('symfony/locale is buggy');
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLocales = $information->getAllAllowedLocales();
        $this->assertContains('de_DE', $foundLocales);
        $this->assertContains('fr_FR', $foundLocales);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLanguages($intlExtension)
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLanguages = $information->getAllAllowedLanguages();
        $this->assertContains('de_CH', $foundLanguages);
        $this->assertNotContains('de_LI', $foundLanguages);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLanguagesStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension, true);
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock());
        $foundLanguages = $information->getAllAllowedLanguages();
        $this->assertCount(count($this->allowedLocales), $foundLanguages);
        foreach ($foundLanguages as $locale) {
            $this->assertContains($locale, $this->allowedLocales);
        }
    }

    public function testGetPreferredLocales()
    {
        $preferredLocale = array('en', 'de');
        $allowedLocales = array('en', 'fr', 'es');

        $guesserManager = $this->getGuesserManagerMock();
        $guesserManager
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->will($this->returnValue($preferredLocale))
        ;

        $info = new LocaleInformation($this->getMetaValidator($allowedLocales), $guesserManager, $allowedLocales);

        $this->assertEquals(array('en'), $info->getPreferredLocales());
    }

    protected function getGuesserManagerMock()
    {
        return $this->getMockBuilder('Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager')->disableOriginalConstructor()->getMock();
    }
}