<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\LocaleInformation;

use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;
use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use Lunetics\LocaleBundle\Tests\Validator\BaseMetaValidator;
use PHPUnit\Framework\MockObject\MockObject;

class LocaleInformationTest extends BaseMetaValidator
{
    protected $allowedLocales = ['en', 'de', 'fr_CH'];

    /** @var LocaleGuesserManager|MockObject */
    private $guesserManager;

    public function setUp() : void
    {
        parent::setUp();
        $this->guesserManager = $this->createMock(LocaleGuesserManager::class);
    }

    public function testGetAllowedLocalesFromConfiguration() : void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, false, false);
        $information   = new LocaleInformation($metaValidator, $this->guesserManager, new AllowedLocalesProvider($this->allowedLocales));
        $this->assertSame($this->allowedLocales, $information->getAllowedLocalesFromConfiguration());
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocales(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension, false);
        $information   = new LocaleInformation($metaValidator, $this->guesserManager);
        $foundLocales  = $information->getAllAllowedLocales();

        $this->assertContains('en_GB', $foundLocales);
        $this->assertContains('en_US', $foundLocales);
        $this->assertContains('de_CH', $foundLocales);
        $this->assertContains('de_AT', $foundLocales);
        $this->assertContains('fr_CH', $foundLocales);
        $this->assertContains('de', $foundLocales);
        $this->assertContains('en', $foundLocales);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocalesStrict(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension, true);
        $information   = new LocaleInformation($metaValidator, $this->guesserManager);
        $foundLocales  = $information->getAllAllowedLocales();
        $this->assertNotContains('en_US', $foundLocales);
        $this->assertNotContains('de_AT', $foundLocales);
        $this->assertContains('de', $foundLocales);
        $this->assertContains('en', $foundLocales);
        $this->assertContains('fr_CH', $foundLocales);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLocalesLanguageIdenticalToRegion(bool $intlExtension) : void
    {
        $this->markTestSkipped('symfony/locale is buggy');
        $metaValidator = $this->getMetaValidator($this->allowedLocales, $intlExtension, false);
        $information   = new LocaleInformation($metaValidator, $this->guesserManager);
        $foundLocales  = $information->getAllAllowedLocales();
        $this->assertContains('de_DE', $foundLocales);
        $this->assertContains('fr_FR', $foundLocales);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLanguages(bool $intlExtension) : void
    {
        $metaValidator  = $this->getMetaValidator($this->allowedLocales, $intlExtension, false);
        $information    = new LocaleInformation($metaValidator, $this->guesserManager);
        $foundLanguages = $information->getAllAllowedLanguages();
        $this->assertContains('de_CH', $foundLanguages);
        $this->assertNotContains('it_IT', $foundLanguages);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testGetAllAllowedLanguagesStrict(bool $intlExtension) : void
    {
        $metaValidator  = $this->getMetaValidator($this->allowedLocales, $intlExtension, true);
        $information    = new LocaleInformation($metaValidator, $this->guesserManager);
        $foundLanguages = $information->getAllAllowedLanguages();
        $this->assertCount(count($this->allowedLocales), $foundLanguages);
        foreach ($foundLanguages as $locale) {
            $this->assertContains($locale, $this->allowedLocales);
        }
    }

    private function getLocaleInformation(array $preferredLocale) : LocaleInformation
    {
        $allowedLocales = ['en', 'fr', 'es'];

        $this->guesserManager
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn($preferredLocale);

        return new LocaleInformation($this->getMetaValidator($allowedLocales, false, false), $this->guesserManager, new AllowedLocalesProvider($allowedLocales));

    }

    public function testGetPreferredLocales() : void
    {
        $info = $this->getLocaleInformation(['en', 'de']);
        $this->assertEquals(['en'], $info->getPreferredLocales());
    }


    /**
     * Make sure we don't crash when a browser fails to define a preferred language.
     */
    public function testGetPreferredLocalesNoneDefined() : void
    {
        $info = $this->getLocaleInformation([]);
        $this->assertEquals([], $info->getPreferredLocales());
    }
}
