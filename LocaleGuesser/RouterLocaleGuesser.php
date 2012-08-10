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
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class RouterLocaleGuesser implements LocaleGuesserInterface
{
    private $checkQuery;

    private $identifiedLocale = null;

    /**
     * Constructor
     *
     * @param boolean $checkQuery
     */
    public function __construct($checkQuery = true)
    {
        $this->checkQuery = $checkQuery;
    }

    /**
     * Method that guess the locale based on the Router parameters
     *
     * The Symfony\Component\HttpKernel\EventListener\LocaleListener
     * does not check for query parameters
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return boolean                                   true if locale is detected, false otherwise
     */
    public function guessLocale(Request $request)
    {
        if ($this->checkQuery) {
            if ($request->query->has('_locale')) {
                $this->identifiedLocale = $request->query->get('_locale');

                return true;
            }
        }
        if ($locale = $request->attributes->get('_locale')) {
            $this->identifiedLocale = $locale;

            return true;
        }

        return false;
    }

    /**
     * Returns the Identified Locale
     *
     * @return string If a locale has been identified in the route parameters
     * @return false  otherwise
     */
    public function getIdentifiedLocale()
    {
        if (null === $this->identifiedLocale) {
            return false;
        }

        return $this->identifiedLocale;
    }
}
