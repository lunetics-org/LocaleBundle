<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\Form\Extension\ChoiceList;

use Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList;
use PHPUnit\Framework\TestCase;

/**
 * Test for the LocaleInformation
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleChoiceListTest extends TestCase
{

    public function testDefaultChoiceList()
    {
        $information = $this->getLocaleInformation();
        $information->expects($this->once())
                ->method('getAllAllowedLanguages')
                ->will($this->returnValue(array('nl', 'nl_BE', 'de', 'de_AT', 'de_CH')));

        $information->expects($this->once())
                ->method('getPreferredLocales')
                ->will($this->returnValue(array()));

        $list = new LocaleChoiceList($information);
        $result = array('nl', 'de');
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testStrictMode()
    {
        $information = $this->getLocaleInformation();
        $information->expects($this->once())
                ->method('getAllowedLocalesFromConfiguration')
                ->will($this->returnValue(array('en', 'de', 'nl', 'de_AT')));

        $information->expects($this->once())
                ->method('getPreferredLocales')
                ->will($this->returnValue(array()));

        $list = new LocaleChoiceList($information, true, true);
        $result = array('en', 'de', 'nl');
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testNotLanguagesOnly()
    {
        $information = $this->getLocaleInformation();
        $information->expects($this->once())
                ->method('getAllAllowedLanguages')
                ->will($this->returnValue(array('nl', 'nl_BE', 'de', 'de_AT', 'de_CH')));

        $information->expects($this->once())
                ->method('getPreferredLocales')
                ->will($this->returnValue(array()));

        $list = new LocaleChoiceList($information, false);
        $result = array('nl', 'nl_BE', 'de', 'de_AT', 'de_CH');
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testNotLanguagesOnlyStrictMode()
    {
        $information = $this->getLocaleInformation();
        $information->expects($this->once())
                ->method('getAllowedLocalesFromConfiguration')
                ->will($this->returnValue(array('en', 'de', 'nl', 'de_AT')));

        $information->expects($this->once())
                ->method('getPreferredLocales')
                ->will($this->returnValue(array()));

        $list = new LocaleChoiceList($information, false, true);
        $result = array('en', 'de', 'nl', 'de_AT');
        $this->assertEquals($result, array_values($list->getOriginalKeys()));
    }

    public function testPreferredLocalesSorted()
    {
        $information = $this->getLocaleInformation();
        $information->expects($this->once())
                ->method('getAllAllowedLanguages')
                ->will($this->returnValue(array('tr', 'en', 'de', 'nl', 'fr')));

        $information->expects($this->once())
                ->method('getPreferredLocales')
                ->will($this->returnValue(array('de', 'nl', 'en')));

        $list = new LocaleChoiceList($information);
        $preferredResult = $list->getPreferredChoices();

        $this->assertEquals('de', $preferredResult[0]);
        $this->assertEquals('nl', $preferredResult[1]);
        $this->assertEquals('en', $preferredResult[2]);
    }

    public function getLocaleInformation()
    {
        return $this->getMockBuilder('Lunetics\LocaleBundle\LocaleInformation\LocaleInformation')->disableOriginalConstructor()->getMock();
    }
}
