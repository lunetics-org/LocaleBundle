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
    
    public function __construct($name, $ttl, $path, $domain = null, $secure, $httpOnly)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }
    
    public function getLocaleCookie($locale)
    {
        $value = $locale;
        $expire = $this->computeExpireTime();
        $cookie = new Cookie($this->name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
        return $cookie;
    }
    
    private function computeExpireTime()
    {
        $expiretime = time() + $this->ttl;
        $date = new \DateTime($expiretime);
        return $date;
    }
}