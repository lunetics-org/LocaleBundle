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

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class BestLocaleMatcherTest extends \PHPUnit_Framework_TestCase
{
     /**
     * @dataProvider getTestDataForBestLocaleMatcher
     */
    public function testMatch($locale, $allowed, $expected)
    {
        $matcher = new DefaultBestLocaleMatcher($allowed);

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
