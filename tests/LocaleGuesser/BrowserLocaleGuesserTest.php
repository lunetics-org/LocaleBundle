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
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BrowserLocaleGuesserTest extends TestCase
{
    /** @var MetaValidator|MockObject */
    private $validator;

    /** @var BrowserLocaleGuesser */
    private $guesser;

    protected function setUp() : void
    {
        $this->validator = $this->createMock(MetaValidator::class);
        $this->guesser   = new BrowserLocaleGuesser($this->validator);
    }

    public function testGuesserExtendsInterface() : void
    {
        $this->assertInstanceOf(LocaleGuesserInterface::class, $this->guesser);
    }

    public function testNoPreferredLocale() : void
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', '');
        $this->assertFalse($this->guesser->guessLocale($request));
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsIdentifiedFromBrowser() : void
    {
        $request = $this->getRequestWithBrowserPreferences();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);

        $this->assertTrue($this->guesser->guessLocale($request));
        $this->assertEquals('fr_FR', $this->guesser->getIdentifiedLocale());
    }


    public function testLocaleIsIdentifiedFromBrowserTestFallbackForNoIntlExtension() : void
    {
        $request = $this->getRequestWithBrowserPreferences();

        $this->validator
            ->expects($this->once())
            ->method('isAllowed')
            ->with('fr_FR')
            ->willReturn(true);

        $this->assertTrue($this->guesser->guessLocale($request));
        $this->assertEquals('fr_FR', $this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguage() : void
    {
        $request = $this->getRequestWithBrowserPreferences();

        $this->validator
            ->method('isAllowed')
            ->willReturn(false);
        $this->assertFalse($this->guesser->guessLocale($request));
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function testLocaleIsNotIdentifiedIsNoMatchedLanguageTestFallbackForNoIntlExtension() : void
    {
        $request = $this->getRequestWithBrowserPreferences();

        $this->validator
            ->method('isAllowed')
            ->willReturn(false);

        $this->guesser->guessLocale($request);
        $this->assertFalse($this->guesser->getIdentifiedLocale());
    }

    public function correctLocales() : array
    {
        return [
            'strict 1'   => [['en', 'de', 'fr'], 'fr', true],
            'unstrict 1' => [['en', 'de', 'fr'], 'fr_CH', false],
            'strict 2'   => [['de', 'en_GB', 'fr', 'fr_FR'], 'fr', true],
            'unstrict 2' => [['de', 'en_GB', 'fr', 'fr_FR'], 'fr_CH', false],
            'strict 3'   => [['en', 'en_GB', 'fr', 'fr'], 'fr', true],
            'unstrict 3' => [['en', 'en_GB', 'fr', 'fr_CH'], 'fr_CH', false],
            'strict 4'   => [['fr', 'en_GB'], 'fr', true],
            'unstrict 4' => [['fr', 'en_GB'], 'fr_CH', false],
            'strict 5'   => [['fr_LI', 'en'], 'en', true],
            'unstrict 5' => [['fr_LI', 'en'], 'en_GB', false],
        ];
    }

    /**
     * @dataProvider correctLocales
     */
    public function testEnsureCorrectLocaleForAllowedLocales(array $allowedLocales, string $result, bool $strict) : void
    {
        $request = $this->getRequestWithBrowserPreferencesMultipleLangLocales();

        // Emulate a simple validator for strict mode
        $this->validator
            ->expects($this->atLeastOnce())
            ->method('isAllowed')
            ->willReturnCallback(
                static function ($v) use ($allowedLocales, $strict) {
                    if (in_array($v, $allowedLocales, true)) {
                        return true;
                    }

                    if (! $strict) {
                        $splittedLocale = explode('_', $v);
                        $v              = count($splittedLocale) > 1 ? $splittedLocale[0] : $v;

                        return in_array($v, $allowedLocales, true);
                    }

                    return false;
                }
            );

        $this->assertTrue($this->guesser->guessLocale($request));
        $this->assertEquals($result, $this->guesser->getIdentifiedLocale());
    }

    private function getRequestWithBrowserPreferences() : Request
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-FR,fr;q=0.1,en-US;q=0.6,en;q=0.4');

        return $request;
    }

    private function getRequestWithBrowserPreferencesMultipleLangLocales() : Request
    {
        $request = Request::create('/');
        $request->headers->set('Accept-language', 'fr-CH,fr;q=0.8,fr-FR;q=0.7, en-GB;q=0.6,en;q=0.5');

        return $request;
    }
}
