<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleCookie
{
    private $name;
    private $ttl;
    private $path;
    private $domain;
    private $secure;
    private $httpOnly;
    private $setOnChange;

    public function __construct($name, $ttl, $path, $domain, $secure, $httpOnly, $setOnChange)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->setOnChange = $setOnChange;
    }

    public function getLocaleCookie($locale)
    {
        $value = $locale;
        $expire = $this->computeExpireTime();
        $cookie = new Cookie($this->name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);

        return $cookie;
    }

    public function setCookieOnChange()
    {
        return $this->setOnChange;
    }

    private function computeExpireTime()
    {
        $expiretime = time() + $this->ttl;
        $date = new \DateTime();
        $date->setTimestamp($expiretime);

        return $date;
    }

    public function getName()
    {
        return $this->name;
    }
}
