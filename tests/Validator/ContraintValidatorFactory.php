<?php

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

class ContraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /** @var array */
    protected $validators = [];

    /** @var LocaleValidator */
    private $localeValidator;

    /** @var LocaleAllowedValidator */
    private $localeAllowedValidator;


    public function __construct(LocaleValidator $localeValidator, LocaleAllowedValidator $localeAllowedValidator)
    {
        $this->localeValidator        = $localeValidator;
        $this->localeAllowedValidator = $localeAllowedValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if ($className === 'lunetics_locale.validator.locale') {
            $this->validators[$className] = $this->localeValidator;
        }

        if ($className === 'lunetics_locale.validator.locale_allowed') {
            $this->validators[$className] = $this->localeAllowedValidator;
        }

        return $this->validators[$className];
    }
}
