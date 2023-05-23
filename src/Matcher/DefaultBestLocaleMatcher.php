<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Matcher;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 */
class DefaultBestLocaleMatcher implements BestLocaleMatcher
{
    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocaleProvider;

    /**
     * Constructor
     *
     * @param array  $allowedLocales array of valid locales
     */
    public function __construct(AllowedLocalesProvider $allowedLocales)
    {
        $this->allowedLocaleProvider = $allowedLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function match($locale)
    {
        $allowedLocales = $this->allowedLocaleProvider->getAllowedLocales();

        uasort($allowedLocales, function ($a, $b) {
            return strlen($b)-strlen($a);
        });
        foreach ($allowedLocales as $allowedLocale) {
            if (strpos($locale, $allowedLocale)===0) {
                return $allowedLocale;
            }
        }
        // Checks for the first part of the locale and matches only the language part
        $splitLanguage = preg_split('/[_-]/', $locale);
        if (count($splitLanguage) > 1) {
            $locale = $splitLanguage[0];
        }
        foreach ($allowedLocales as $allowedLocale) {
            if (str_starts_with($allowedLocale, $locale)) {
                return $allowedLocale;
            }
        }
        return false;
    }
}
