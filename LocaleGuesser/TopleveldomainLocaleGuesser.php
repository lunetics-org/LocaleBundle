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

use Lunetics\LocaleBundle\LocaleInformation\TopleveldomainLocaleMap;
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\Validator\MetaValidator;

/**
 * Locale Guesser for detecting the locale from the toplevel domain
 *
 * @author Ivo Bathke <ivo.bathke@gmail.com>
 */
class TopleveldomainLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var TopleveldomainLocaleMap
     */
    private $topleveldomainLocaleMap;

    /**
     * @param MetaValidator $metaValidator
     * @param TopleveldomainLocaleMap $topleveldomainLocaleMap
     */
    public function __construct(MetaValidator $metaValidator, TopleveldomainLocaleMap $topleveldomainLocaleMap)
    {
        $this->metaValidator = $metaValidator;
        $this->topleveldomainLocaleMap = $topleveldomainLocaleMap;
    }

    /**
     * Guess the locale based on the topleveldomain
     *
     * @param Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        $topLevelDomain = substr(strrchr($request->getHost(), '.'), 1);

        //use topleveldomain as locale
        $locale = $topLevelDomain;
        //see if we have some additional mappings
        if ($topLevelDomain && $this->topleveldomainLocaleMap->getLocale($topLevelDomain)) {
            $locale = $this->topleveldomainLocaleMap->getLocale($topLevelDomain);
        }
        //now validate
        if (false !== $locale && $this->metaValidator->isAllowed($locale)) {
            $this->identifiedLocale = $locale;
            return true;
        }

        return false;
    }
} 