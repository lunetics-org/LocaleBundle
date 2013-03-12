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

use Symfony\Component\Locale\Locale as SymfonyLocale;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


/**
 * Validator for a locale
 *
 * @author Matthias Bredin <mb@lunetics.com>
 */
class LocaleValidator extends ConstraintValidator
{
    /**
     * @var bool
     */
    private $intlExtension;

    /**
     * @var array
     */
    private $iso3166;

    /**
     * @var array
     */
    private $iso639;

    /**
     * @var array
     */
    private $script;

    /**
     * Constructor
     *
     * @param bool  $intlExtension Wether the intl extension is installed
     * @param array $iso3166       Array of valid iso3166 codes
     * @param array $iso639        Array of valid iso639 codes
     * @param array $script        Array of valid locale scripts
     */
    public function __construct($intlExtension = false, array $iso3166 = array(), array $iso639 = array(), array $script = array())
    {
        $this->intlExtension = $intlExtension;
        $this->iso3166 = $iso3166;
        $this->iso639 = $iso639;
        $this->script = $script;
    }

    /**
     * Validates a Locale
     *
     * @param string     $locale     The locale to be validated
     * @param Constraint $constraint Locale Constraint
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
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

        if ($this->intlExtension) {
            $primary = SymfonyLocale::getPrimaryLanguage($locale);
            $region  = SymfonyLocale::getRegion($locale);
            $locales = SymfonyLocale::getLocales();

            if ((null !== $region && strtolower($primary) != strtolower($region)) && !in_array($locale, $locales) && !in_array($primary, $locales)) {
                $this->context->addViolation($constraint->message, array('%string%' => $locale));
            }
        } else {
            $splittedLocale = explode('_', $locale);
            $splitCount = count($splittedLocale);

            if ($splitCount == 1) {
                $primary = $splittedLocale[0];
                if (!in_array($primary, $this->iso639)) {
                    $this->context->addViolation($constraint->message, array('%string%' => $locale));
                }
            } elseif ($splitCount == 2) {
                $primary = $splittedLocale[0];
                $region = $splittedLocale[1];
                if (!in_array($primary, $this->iso639) && !in_array($region, $this->iso3166)) {
                    $this->context->addViolation($constraint->message, array('%string%' => $locale));
                }
            } elseif ($splitCount > 2) {
                $primary = $splittedLocale[0];
                $script = $splittedLocale[1];
                $region = $splittedLocale[2];
                if (!in_array($primary, $this->iso639) && !in_array($region, $this->iso3166) && !in_array($script, $this->script)) {
                    $this->context->addViolation($constraint->message, array('%string%' => $locale));
                }
            }
        }
    }
}
