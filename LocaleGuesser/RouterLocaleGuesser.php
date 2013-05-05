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
class RouterLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * Constructor
     *
     * @param MetaValidator $metaValidator MetaValidator
     */
    public function __construct(MetaValidator $metaValidator)
    {
        $this->metaValidator = $metaValidator;
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
        if ($locale = $request->attributes->get('_locale')) {
            if ($localeValidator->isAllowed($locale)) {
                $this->identifiedLocale = $locale;
            }

            return true;
        }

        return false;
    }
}
