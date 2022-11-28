<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Tests\EventListener;

use Lunetics\LocaleBundle\Matcher\DefaultBestLocaleMatcher;
use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class BestLocaleMatcherTest extends TestCase
{
    /**
     * @dataProvider getTestDataForBestLocaleMatcher
     *
     * @param string $locale
     * @param array $allowed
     * @param string|bool $expected
     */
    public function testMatch($locale, $allowed, $expected)
    {
        $matcher = new DefaultBestLocaleMatcher(new AllowedLocalesProvider($allowed));

        $this->assertEquals($expected, $matcher->match($locale));
    }

    public function getTestDataForBestLocaleMatcher()
    {
        return array(
            array('fr', array('fr'), 'fr'),
            array('fr_FR', array('fr', 'fr_FR'), 'fr_FR'),
            array('fr_FR', array('fr_FR', 'fr'), 'fr_FR'),
            array('fr_FR', array('fr'), 'fr'),
            array('fr_FR', array('fr_FR'), 'fr_FR'),
            array('fr_FR', array('en_GB'), false),
        );
    }
}
