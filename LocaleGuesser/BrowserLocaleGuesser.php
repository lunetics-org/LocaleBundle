<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;

/**
 * Locale Guesser for detecing the locale from the browser Accept-language string
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class BrowserLocaleGuesser implements LocaleGuesserInterface
{
    private $allowedLocales;

    private $identifiedLocale;

    private $intlExtension;

    /**
     * Constructor
     *
     * @param array $allowedLocales Array of allowed locales
     */
    public function __construct(array $allowedLocales)
    {
        $this->allowedLocales = $allowedLocales;
        $this->intlExtension = extension_loaded('intl');
    }

    /**
     * Guess the locale based on browser settings
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        // Get the preferred locale from the Browser.
        $preferredLocale = $request->getPreferredLanguage();
        $availableLocales = $request->getLanguages();
        $allowedLocales = $this->allowedLocales;


        if (!$preferredLocale OR count($availableLocales) === 0) {
            return false;
        }

        // If the preferred primary locale is allowed, return the locale.
        if (in_array($preferredLocale, $allowedLocales)) {
            $this->identifiedLocale = $preferredLocale;

            return true;
        }

        if ($this->intlExtension) {
            $primaryLanguage = \Locale::getPrimaryLanguage($preferredLocale);
        } else {
            $primaryLanguage = $this->getPrimaryLanguage($preferredLocale);
        }

        if (!in_array($primaryLanguage, $allowedLocales)) {

            // Try to find a full locale (Language + country)
            $matchLocale = function ($v) use ($allowedLocales) {
                if (in_array($v, $allowedLocales)) {
                    return true;
                }

                return false;
            };

            $result = array_values(array_filter($availableLocales, $matchLocale));

            if (!empty($result)) {
                $this->identifiedLocale = $result[0];

                return true;
            }

            // Try to find a language
            $availableLanguages = $this->compileLanguageArray($availableLocales);
            $result = array_values(array_filter($availableLanguages, $matchLocale));
            if (!empty($result)) {
                $this->identifiedLocale = $result[0];

                return true;
            }
        } else {
            $this->identifiedLocale = $preferredLocale;

            return true;
        }

        return false;
    }

    /**
     * Fallback function for fetching the primary language, if no intl extension is installed.
     *
     * @param string $locale
     *
     * @return null|string
     */
    private function getPrimaryLanguage($locale)
    {
        $primaryLanguage = substr($locale, 0, 2);
        if (preg_match('/[a-z]{2}/', $primaryLanguage)) {
            return $primaryLanguage;
        }

        return null;
    }

    /**
     * Compiles a list of all available languages from a locale list
     *
     * @param array $availableLocales
     *
     * @return array
     */
    private function compileLanguageArray(array $availableLocales = array())
    {
        $providedLanguages = array();
        foreach ($availableLocales as $locale) {
            if ($this->intlExtension) {
                $language = $this->getPrimaryLanguage($locale);
            } else {
                $language = \Locale::getPrimaryLanguage($locale);
            }
            if ($language) {
                $providedLanguages[] = $language;
            }
        }

        return array_unique($providedLanguages);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
