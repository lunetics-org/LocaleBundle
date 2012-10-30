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

use Symfony\Component\Locale\Locale;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use Lunetics\LocaleBundle\LocaleGuesser\LocaleGuesserManager;

/**
 * Information about Locales
 */
class LocaleInformation
{
    private $metaValidator;
    private $manager;
    private $allowedLocales;

    /**
     * @param MetaValidator        $metaValidator  Validator
     * @param LocaleGuesserManager $manager        LocaleGuesserManager
     * @param array                $allowedLocales Allowed locales from config
     */
    public function __construct(MetaValidator $metaValidator, LocaleGuesserManager $manager, $allowedLocales = array())
    {
        $this->metaValidator = $metaValidator;
        $this->manager = $manager;
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * Returns the configuration of allowed locales
     *
     * @return array
     */
    public function getAllowedLocalesFromConfiguration()
    {
        return $this->allowedLocales;
    }

    /**
     * Returns an array of all allowed locales based on the configuration
     *
     * @return array|bool
     */
    public function getAllAllowedLocales()
    {
        return $this->filterAllowed(Locale::getLocales());
    }

    /**
     * Returns an array of all allowed languages based on the configuration
     *
     * @return array|bool
     */
    public function getAllAllowedLanguages()
    {
        return $this->filterAllowed(Locale::getLanguages());
    }

    /**
     * Returns an array of preferred locales
     *
     * @return array
     */
    public function getPreferredLocales()
    {
        $preferredLocales = $this->filterAllowed($this->manager->getPreferredLocales());

        return $preferredLocales;
    }

    /**
     * Filter function which returns locales / languages
     *
     * @param array $localeList
     *
     * @return array|bool
     */
    private function filterAllowed(array $localeList)
    {
        $validator = $this->metaValidator;
        $matchLocale = function ($locale) use ($validator) {
            return $validator->isAllowed($locale);
        };
        $availableLocales = array_values(array_filter($localeList, $matchLocale));
        if (!empty($availableLocales)) {
            return $availableLocales;
        }

        return false;
    }
}