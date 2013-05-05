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
 * Locale Guesser for detecing the locale from the browser Accept-language string
 *
 * @author Matthias Breddin <mb@lunetics.com>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com>
 */
class BrowserLocaleGuesser implements LocaleGuesserInterface
{
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
     */
    public function __construct(MetaValidator $metaValidator)
    {
        $this->metaValidator = $metaValidator;
    }

    /**
     * Guess the locale based on the browser settings
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        $validator = $this->metaValidator;
        // Get the preferred locale from the Browser.
        $preferredLocale = $request->getPreferredLanguage();
        $availableLocales = $request->getLanguages();

        if (!$preferredLocale OR count($availableLocales) === 0) {
            return false;
        }

        // If the preferred primary locale is allowed, return the locale.
        if ($validator->isAllowed($preferredLocale)) {
            $this->identifiedLocale = $preferredLocale;

            return true;
        }

        // Get a list of available and allowed locales and return the first result
        $matchLocale = function ($v) use ($validator) {
            return $validator->isAllowed($v);
        };

        $result = array_values(array_filter($availableLocales, $matchLocale));
        if (!empty($result)) {
            $this->identifiedLocale = $result[0];

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
