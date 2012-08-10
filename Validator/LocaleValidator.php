<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Validator;

use Symfony\Component\Locale\Locale;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class LocaleValidator
{
    /**
     *
     * @param  string                    $locale The locale to be validated
     * @return string                    The cleaned locale if needed
     * @throws \InvalidArgumentException When the locale is not a valid locale
     */
    public function validate($locale)
    {
        $splittedLocale = explode('_', $locale);
        $primary = count($splittedLocale) > 1 ? $splittedLocale[0] : $locale;
        $variant = count($splittedLocale) > 1 ? $splittedLocale[1] : null;
        if (!in_array($primary, Locale::getLocales())) {
            throw new \InvalidArgumentException(sprintf('The locale %s is not a valid locale', $primary));
        }
        //If a variant is set and is not different from the primary language, check for variant validity
        if (null !== $variant && strtolower($primary) != strtolower($variant)) {
            $loc = strtolower($primary).'_'.strtoupper($variant);
            if (!in_array($loc, Locale::getLocales())) {
                throw new \InvalidArgumentException(sprintf('The locale %s is not a valid locale', $primary));
            }
        }

        return true;
    }
}
