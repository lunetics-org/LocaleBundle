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
 * Locale Guesser for detecting the locale from the subdomain
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var string
     */
    private $regionSeparator;

    /**
     * @param MetaValidator $metaValidator
     * @param string $regionSeparator
     */
    public function __construct(MetaValidator $metaValidator, $regionSeparator = '_')
    {
        $this->metaValidator = $metaValidator;
        $this->regionSeparator = $regionSeparator;
    }

    /**
     * Guess the locale based on the subdomain
     *
     * @param Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        $subdomain = strstr($request->getHost(), '.', true);

        if ('_' !== $this->regionSeparator) {
            $subdomain = str_replace($this->regionSeparator, '_', $subdomain);
        }

        if (false !== $subdomain && $this->metaValidator->isAllowed($subdomain)) {
            $this->identifiedLocale = $subdomain;

            return true;
        }

        return false;
    }
}
