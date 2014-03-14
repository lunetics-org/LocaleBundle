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
    private $allowedLocalesProvider;

    /**
     * @param MetaValidator           $metaValidator           Validator
     * @param LocaleGuesserManager    $manager                 LocaleGuesserManager
     * @param AllowedLocalesProvider  $allowedLocalesProvider  Allowed locales
     */
    public function __construct(MetaValidator $metaValidator, LocaleGuesserManager $manager, AllowedLocalesProvider $allowedLocalesProvider = null)
    {
        $this->metaValidator = $metaValidator;
        $this->manager = $manager;
        $this->allowedLocalesProvider = $allowedLocalesProvider;
    }

    /**
     * Returns the configuration of allowed locales
     *
     * @return array
     */
    public function getAllowedLocalesFromConfiguration()
    {
        if (null !== $this->allowedLocalesProvider) {
            return $this->allowedLocalesProvider->getAllowedLocales();
        } else {
            return array();
        }
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

        // Make sure we return an array even if no preferred locale can be found
        return $preferredLocales ?: array();

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