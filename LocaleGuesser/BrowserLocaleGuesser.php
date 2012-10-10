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
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class BrowserLocaleGuesser implements LocaleGuesserInterface
{
    private $defaultLocale;

    private $allowedLocales;

    private $identifiedLocale;

    /**
     * Constructor
     *
     * @param string $defaultLocale  The default locale
     * @param array  $allowedLocales Array of allowed locales
     */
    public function __construct($defaultLocale, array $allowedLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->allowedLocales = $allowedLocales;
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
        // Get the Preferred Language from the Browser
        $preferredLanguage = $request->getPreferredLanguage();
        $providedLanguages = $request->getLanguages();

        if (!$preferredLanguage OR count($providedLanguages) === 0) {
            return false;
        }

        if (!in_array(\Locale::getPrimaryLanguage($preferredLanguage), $this->allowedLocales)) {
            $availableLanguages = $this->allowedLocales;
            $map = function($v) use ($availableLanguages) {
                if (in_array(\Locale::getPrimaryLanguage($v), $availableLanguages)) {
                    return true;
                }
            };
            $result = array_values(array_filter($providedLanguages, $map));
            if (!empty($result)) {
                $this->identifiedLocale = $result[0];

                return true;
            }
        } else {
            $this->identifiedLocale = $preferredLanguage;

            return true;
        }

        return false;
    }

    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
