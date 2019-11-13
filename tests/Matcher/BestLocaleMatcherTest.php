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

namespace Lunetics\LocaleBundle\Tests\Matcher;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Matcher\DefaultBestLocaleMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class BestLocaleMatcherTest extends TestCase
{
    /**
     * @dataProvider getTestDataForBestLocaleMatcher
     *
     * @param string      $locale
     * @param array       $allowed
     * @param string|bool $expected
     */
    public function testMatch(string $locale, array $allowed, $expected) : void
    {
        $matcher = new DefaultBestLocaleMatcher(new AllowedLocalesProvider($allowed));

        $this->assertSame($expected, $matcher->match($locale));
    }

    public function getTestDataForBestLocaleMatcher() : array
    {
        return [
            ['fr', ['fr'], 'fr'],
            ['fr_FR', ['fr', 'fr_FR'], 'fr_FR'],
            ['fr_FR', ['fr_FR', 'fr'], 'fr_FR'],
            ['fr_FR', ['fr'], 'fr'],
            ['fr_FR', ['fr_FR'], 'fr_FR'],
            ['fr_FR', ['en_GB'], false],
        ];
    }
}
