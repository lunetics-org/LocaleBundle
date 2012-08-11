<?php

/*
 * This file is part of the LuneticsLocaleBundle package.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Tests\DependencyInjection;

use Lunetics\LocaleBundle\Cookie\LocaleCookie;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleCookieTest extends \PHPUnit_Framework_TestCase
{
    public function testCookieParamsAreSet()
    {
        $localeCookie = new LocaleCookie('lunetics_locale', 86400, '/', null, false, true, false, true);
        $cookie = $localeCookie->getLocaleCookie('en');
        $this->assertTrue($cookie instanceof Cookie);
        $this->assertEquals('lunetics_locale', $cookie->getName());
        $this->assertEquals('en', $cookie->getValue());
        $this->assertEquals('/', $cookie->getPath());
        $this->assertEquals(null, $cookie->getDomain());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertFalse($cookie->isSecure());
        print_r($cookie->getExpiresTime());
        $this->assertTrue($cookie->getExpiresTime() > time());
        $this->assertTrue($cookie->getExpiresTime() < time() + 86400);
    }

    public function testCookieExpiresDateTime()
    {

    }
}
