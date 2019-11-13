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
use Lunetics\LocaleBundle\Validator\MetaValidator;
use Symfony\Component\HttpFoundation\Request;

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
     * @param MetaValidator           $metaValidator
     * @param TopleveldomainLocaleMap $topleveldomainLocaleMap
     */
    public function __construct(MetaValidator $metaValidator, TopleveldomainLocaleMap $topleveldomainLocaleMap)
    {
        $this->metaValidator           = $metaValidator;
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

        if (! $topLevelDomain) {
            return false;
        }
        $resolvedLocale = $this->topleveldomainLocaleMap->getLocale($topLevelDomain);

        if (! $resolvedLocale) {
            return false;
        }

        if (! $this->metaValidator->isAllowed($resolvedLocale)) {
            return false;
        }

        $this->identifiedLocale = $resolvedLocale;

        return true;
    }
} 
