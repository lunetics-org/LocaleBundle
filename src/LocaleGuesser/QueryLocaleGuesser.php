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
 * This guesser class checks the query parameter for a var
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class QueryLocaleGuesser extends AbstractLocaleGuesser
{
    /**
     * @var string
     */
    private $metaValidator;

    /**
     * Constructor
     *
     * @param MetaValidator $metaValidator       MetaValidator
     * @param string        $queryParameterName  Query parameter used
     */
    public function __construct(MetaValidator $metaValidator, $queryParameterName = '_locale')
    {
        $this->queryParameterName = $queryParameterName;
        $this->metaValidator = $metaValidator;
    }

    /**
     * Guess the locale based on the query parameter variable
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        $localeValidator = $this->metaValidator;
        if ($request->query->has($this->queryParameterName)) {
            if ($localeValidator->isAllowed($request->query->get($this->queryParameterName))) {
                $this->identifiedLocale = $request->query->get($this->queryParameterName);

                return true;
            }
        }

        return false;
    }
}
