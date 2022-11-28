<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This Metavalidator uses the LocaleAllowed and Locale validators for checks inside a guesser
 */
class MetaValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Checks if a locale is allowed and valid
     *
     * @param string $locale
     *
     * @return bool
     */
    public function isAllowed($locale)
    {
        $errorListLocale = $this->validator->validate($locale, new Locale);
        $errorListLocaleAllowed = $this->validator->validate($locale, new LocaleAllowed);

        return (count($errorListLocale) == 0 && count($errorListLocaleAllowed) == 0);
    }
}
