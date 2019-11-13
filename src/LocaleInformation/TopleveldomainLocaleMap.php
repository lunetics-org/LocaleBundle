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
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopleveldomainLocaleMap
{
    /**
     * @var array
     */
    private $map = array();

    /**
     * @param array $map topleveldomain locale map, [tld => locale]
     */
    function __construct(array $map = array())
    {
        $this->map = $map;
    }

    public function getLocale($topleveldomain)
    {
        if (isset($this->map[$topleveldomain]) && $this->map[$topleveldomain]) {
            return $this->map[$topleveldomain];
        }
        return false;
    }

} 
