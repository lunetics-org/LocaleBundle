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

namespace Lunetics\LocaleBundle\Tests\Session;

use Lunetics\LocaleBundle\Session\LocaleSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleSessionTest extends TestCase
{
    /** @var MockObject|Session */
    private $session;

    /** @var LocaleSession */
    private $localeSession;

    protected function setUp() : void
    {
        $this->session       = $this->createMock(Session::class);
        $this->localeSession = new LocaleSession($this->session);
    }

    public function testHasLocaleChanged() : void
    {
        $localeEn = uniqid('en:', true);
        $localeFr = uniqid('fr:', true);

        $this->session
            ->expects($this->at(0))
            ->method('get')
            ->with('lunetics_locale')
            ->willReturn($localeEn);

        $this->session
            ->expects($this->at(1))
            ->method('get')
            ->with('lunetics_locale')
            ->willReturn($localeFr);

        $this->assertFalse($this->localeSession->hasLocaleChanged($localeEn));
        $this->assertTrue($this->localeSession->hasLocaleChanged($localeEn));
    }

    public function testSetGetLocale() : void
    {
        $locale = uniqid('locale:', true);

        $this->session
            ->expects($this->at(0))
            ->method('set')
            ->with('lunetics_locale', $locale);

        $this->session
            ->expects($this->at(1))
            ->method('get')
            ->with('lunetics_locale', $locale)
            ->willReturn($locale);


        $this->localeSession->setLocale($locale);
        $this->assertEquals($locale, $this->localeSession->getLocale($locale));
    }

    public function testGetSessionVar() : void
    {
        $this->assertEquals('lunetics_locale', $this->localeSession->getSessionVar());
    }
}
