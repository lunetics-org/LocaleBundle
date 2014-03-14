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


class AllowedLocalesProvider
{
    /** @var array */
    protected $allowedLocales;

    public function __construct(array $allowedLocales = null)
    {
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * Return a list of the allowed locales
     *
     * @return array
     */
    public function getAllowedLocales()
    {
        return $this->allowedLocales;
    }

    /**
     * Set the list of the allowed locales
     *
     * @param array $allowedLocales
     */
    public function setAllowedLocales($allowedLocales)
    {
        $this->allowedLocales = $allowedLocales;
    }
}