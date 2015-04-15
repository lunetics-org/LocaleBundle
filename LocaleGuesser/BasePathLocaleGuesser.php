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
 * Locale Guesser for detecting the locale from the base path
 *
 * @author Kevin Archer <ka@kevinarcher.ca>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class BasePathLocaleGuesser extends AbstractLocaleGuesser
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
     * Guess the locale based on the base path
     *
     * @param Request $request
     *
     * @return bool
     */
    public function guessLocale(Request $request)
    {
        $basePath = strstr($request->getBasePath(), '/', true);

        if ('_' !== $this->regionSeparator) {
            $basePath = str_replace($this->regionSeparator, '_', $basePath);
        }

        if (false !== $basePath && $this->metaValidator->isAllowed($basePath)) {
            $this->identifiedLocale = $basePath;

            return true;
        }

        return false;
    }
}
