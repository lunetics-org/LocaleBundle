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

use Lunetics\LocaleBundle\LocaleInformation\DomainLocaleMap;
use Symfony\Component\HttpFoundation\Request;
use Lunetics\LocaleBundle\Validator\MetaValidator;

/**
 * Locale Guesser for detecting the locale from the domain
 *
 * @author Jachim Coudenys <jachimcoudenys@gmail.com>
 */
class DomainLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var DomainLocaleMap
     */
    private $domainLocaleMap;

    /**
     * @param MetaValidator $metaValidator
     * @param DomainLocaleMap $domainLocaleMap
     */
    public function __construct(MetaValidator $metaValidator, DomainLocaleMap $domainLocaleMap)
    {
        $this->metaValidator = $metaValidator;
        $this->domainLocaleMap = $domainLocaleMap;
    }

    /**
     * Guess the locale based on the domain
     *
     * @param Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        $domainParts = array_reverse(explode('.', $request->getHost()));

        $domain = null;
        foreach ($domainParts as $domainPart) {
            if (null === $domain) {
                $domain = $domainPart;
            } else {
                $domain = $domainPart . '.' . $domain;
            }

            if (($locale = $this->domainLocaleMap->getLocale($domain))
                && $this->metaValidator->isAllowed($locale)
            ) {
                $this->identifiedLocale = $locale;
                return true;
            }
        }

        return false;
    }
} 
