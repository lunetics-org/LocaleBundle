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
use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Tests\Validator\BaseMetaValidator;
use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use Symfony\Component\Intl\Intl;

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
        $information = new LocaleInformation($metaValidator, $this->getGuesserManagerMock(), new AllowedLocalesProvider($this->allowedLocales));
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

        $this->assertCount(3, $foundLanguages);
        $this->assertContains('de', $foundLanguages);
        $this->assertContains('de', $foundLanguages);
        $this->assertContains('fr', $foundLanguages);
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

        $this->assertCount(2, $foundLanguages);
        $this->assertContains('de', $foundLanguages);
        $this->assertContains('en', $foundLanguages);
    }

    private function getLocaleInformation(array $preferredLocale)
    {
        $allowedLocales = array('en', 'fr', 'es');

        $guesserManager = $this->getGuesserManagerMock();
        $guesserManager
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->will($this->returnValue($preferredLocale))
        ;

        return new LocaleInformation($this->getMetaValidator($allowedLocales), $guesserManager, new AllowedLocalesProvider($allowedLocales));

    }

    public function testGetPreferredLocales()
    {
        $info = $this->getLocaleInformation(array('en', 'de'));
        $this->assertEquals(array('en'), $info->getPreferredLocales());
    }


    /**
     * Make sure we don't crash when a browser fails to define a preferred language.
     */
    public function testGetPreferredLocalesNoneDefined()
    {
        $info = $this->getLocaleInformation(array());
        $this->assertEquals(array(), $info->getPreferredLocales());
    }

    protected function getGuesserManagerMock()
    {
        return $this->getMockBuilder('Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager')->disableOriginalConstructor()->getMock();
    }
}
