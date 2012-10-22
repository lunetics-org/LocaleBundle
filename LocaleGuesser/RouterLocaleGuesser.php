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
use Lunetics\LocaleBundle\Validator\MetaValidator;

/**
 * Locale Guesser for detecing the locale in the router
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class RouterLocaleGuesser implements LocaleGuesserInterface
{
    /**
     * @var bool
     */
    private $checkQuery;

    /**
     * @var string
     */
    private $identifiedLocale;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * Constructor
     *
     * @param MetaValidator $metaValidator MetaValidator
     * @param boolean       $checkQuery    Wether to check the query for a locale
     */
    public function __construct(MetaValidator $metaValidator, $checkQuery = true)
    {
        $this->metaValidator = $metaValidator;
        $this->checkQuery = $checkQuery;
    }

    /**
     * Method that guess the locale based on the Router parameters
     *
     * @param Request $request
     *
     * @return boolean True if locale is detected, false otherwise
     */
    public function guessLocale(Request $request)
    {
        $localeValidator = $this->metaValidator;
        if ($this->checkQuery && $request->query->has('_locale')) {
            if ($localeValidator->isAllowed($request->query->get('_locale'))) {
                $this->identifiedLocale = $request->query->get('_locale');

                return true;
            }
        }
        if ($locale = $request->attributes->get('_locale')) {
            if ($localeValidator->isAllowed($locale)) {
                $this->identifiedLocale = $locale;
            }

            return true;
        }

        return false;
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
