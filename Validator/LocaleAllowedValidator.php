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

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


/**
 * Validator to check if a locale is allowed by the configuration
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleAllowedValidator extends ConstraintValidator
{
    /**
     * @var AllowedLocalesProvider
     */
    private $allowedLocalesProvider;

    /**
     * @var bool
     */
    private $strictMode;

    /**
     * @var bool
     */
    private $intlExtension;

    /**
     * Constructor
     *
     * @param AllowedLocalesProvider  $allowedLocalesProvider  allowed locales provided by service
     * @param bool                    $strictMode              Match locales strict (e.g. de_DE will not match allowedLocale de)
     * @param bool                    $intlExtension           Whether the intl extension is installed
     */
    public function __construct(AllowedLocalesProvider $allowedLocalesProvider, $strictMode = false, $intlExtension = false)
    {
        $this->allowedLocalesProvider = $allowedLocalesProvider;
        $this->strictMode = $strictMode;
        $this->intlExtension = $intlExtension;
    }

    /**
     * Validates a Locale
     *
     * @param string     $locale     The locale to be validated
     * @param Constraint $constraint Locale Constraint
     *
     * @throws UnexpectedTypeException
     */
    public function validate($locale, Constraint $constraint)
    {
        if (null === $locale || '' === $locale) {
            return;
        }

        if (!is_scalar($locale) && !(is_object($locale) && method_exists($locale, '__toString'))) {
            throw new UnexpectedTypeException($locale, 'string');
        }

        $locale = (string) $locale;

        if ($this->strictMode) {
            if (!in_array($locale, $this->allowedLocalesProvider->getAllowedLocales())) {
                $this->context->addViolation($constraint->message, array('%string%' => $locale));
            }
        } else {
            if ($this->intlExtension) {
                $primary = \Locale::getPrimaryLanguage($locale);
            } else {
                $splittedLocale = explode('_', $locale);
                $primary = count($splittedLocale) > 1 ? $splittedLocale[0] : $locale;
            }

            if (!in_array($locale, $this->allowedLocalesProvider->getAllowedLocales()) && (!in_array($primary, $this->allowedLocalesProvider->getAllowedLocales()))) {
                $this->context->addViolation($constraint->message, array('%string%' => $locale));
            }
        }
    }
}
