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
     * some common top level domain as default set
     * new or edits can be passed via constructor, unset a domain with null
     * @var array
     */
    private $map = array('com' => 'en_US',
                        'org' => 'en_US',
                        'net' => 'en_US',
                        'uk' => 'en_GB',
                        'nz' => 'en_NZ',
                        'ch' => 'de_CH',
                        'at' => 'de_AT');

    /**
     * @param array $map topleveldomain locale map
     */
    function __construct(array $map = array())
    {
        foreach ($map as $topleveldomain => $locale) {
            $this->map[$topleveldomain] = $locale;
        }
    }

    public function getLocale($topleveldomain)
    {
        if (isset($this->map[$topleveldomain]) && $this->map[$topleveldomain]) {
            return $this->map[$topleveldomain];
        }
        return false;
    }

} 