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

namespace Lunetics\LocaleBundle\Tests\Cookie;

use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleCookieTest extends TestCase
{
    public function testCookieParamsAreSet() : void
    {
        $localeCookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, true);
        $cookie       = $localeCookie->getLocaleCookie('en');
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals('lunetics_locale', $cookie->getName());
        $this->assertEquals('en', $cookie->getValue());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertEquals(null, $cookie->getDomain());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertFalse($cookie->isSecure());
    }

    public function testCookieExpiresDateTime() : void
    {
        $localeCookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, true);
        $cookie       = $localeCookie->getLocaleCookie('en');
        $this->assertTrue($cookie->getExpiresTime() > time());
        $this->assertTrue($cookie->getExpiresTime() <= (time() + 86400));
    }
}
