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
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;
use Lunetics\LocaleBundle\Validator\Locale;
use Lunetics\LocaleBundle\Validator\LocaleAllowed;

/**
 * This Metavalidator uses the LocaleAllowed and Locale validators for checks inside a guesser
 */
class MetaValidator
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param ValidatorInterface|LegacyValidatorInterface $validator
     */
    public function __construct($validator)
    {
        if (!$validator instanceof ValidatorInterface && !$validator instanceof LegacyValidatorInterface) {
            throw new \InvalidArgumentException('MetadataValidator accepts either the new or the old ValidatorInterface, '.get_class($validator).' was injected instead.');
        }
        
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
        if ($this->validator instanceof ValidatorInterface) {
            $errorListLocale = $this->validator->validate($locale, new Locale);
            $errorListLocaleAllowed = $this->validator->validate($locale, new LocaleAllowed);
        } else {
            // for Symfony <2.5 compatibility
            $errorListLocale = $this->validator->validateValue($locale, new Locale);
            $errorListLocaleAllowed = $this->validator->validateValue($locale, new LocaleAllowed);
        }

        return (count($errorListLocale) == 0 && count($errorListLocaleAllowed) == 0);
    }
}
