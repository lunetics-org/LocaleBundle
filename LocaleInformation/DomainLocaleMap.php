<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\LocaleInformation;

/**
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleMap
{
    /**
     * @var array
     */
    private $map = array();

    /**
     * @param array $map domain locale map, [version.tld => locale, sub.version2.tld => locale]
     */
    function __construct(array $map = array())
    {
        $this->map = $map;
    }

    /**
     * Get the locale for a given domain.
     *
     * @param string $domain
     *
     * @return string|bool
     */
    public function getLocale($domain)
    {
        if (isset($this->map[$domain]) && $this->map[$domain]) {
            return $this->map[$domain];
        }
        return false;
    }

} 
