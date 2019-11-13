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

namespace Lunetics\LocaleBundle\Tests\Form\Extension\ChoiceList;

use Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList;
use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LocaleChoiceListTest extends TestCase
{
    /** @var LocaleInformation|MockObject */
    private $localeInformation;

    public function setUp() : void
    {
        $this->localeInformation = $this->createMock(LocaleInformation::class);
    }

    public function testDefaultChoiceList() : void
    {
        $this->localeInformation
            ->expects($this->once())
            ->method('getAllAllowedLanguages')
            ->willReturn(['nl', 'nl_BE', 'de', 'de_AT', 'de_CH']);

        $this->localeInformation
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn([]);

        $list   = new LocaleChoiceList($this->localeInformation);
        $result = ['nl', 'de'];
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testStrictMode() : void
    {
        $this->localeInformation
            ->expects($this->once())
            ->method('getAllowedLocalesFromConfiguration')
            ->willReturn(['en', 'de', 'nl', 'de_AT']);

        $this->localeInformation
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn([]);

        $list   = new LocaleChoiceList($this->localeInformation, true, true);
        $result = ['en', 'de', 'nl'];
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testNotLanguagesOnly() : void
    {
        $this->localeInformation
            ->expects($this->once())
            ->method('getAllAllowedLanguages')
            ->willReturn(['nl', 'nl_BE', 'de', 'de_AT', 'de_CH']);

        $this->localeInformation->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn([]);

        $list   = new LocaleChoiceList($this->localeInformation, false);
        $result = ['nl', 'nl_BE', 'de', 'de_AT', 'de_CH'];
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testNotLanguagesOnlyStrictMode() : void
    {
        $this->localeInformation->expects($this->once())
            ->method('getAllowedLocalesFromConfiguration')
            ->willReturn(['en', 'de', 'nl', 'de_AT']);

        $this->localeInformation
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn([]);

        $list   = new LocaleChoiceList($this->localeInformation, false, true);
        $result = ['en', 'de', 'nl', 'de_AT'];
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testPreferredLocalesSorted() : void
    {
        $this->localeInformation
            ->expects($this->once())
            ->method('getAllAllowedLanguages')
            ->willReturn(['tr', 'en', 'de', 'nl', 'fr']);

        $this->localeInformation
            ->expects($this->once())
            ->method('getPreferredLocales')
            ->willReturn(['de', 'nl', 'en']);

        $list            = new LocaleChoiceList($this->localeInformation);
        $preferredResult = $list->getPreferredChoices();

        $this->assertEquals('de', $preferredResult[0]);
        $this->assertEquals('nl', $preferredResult[1]);
        $this->assertEquals('en', $preferredResult[2]);
    }
}
