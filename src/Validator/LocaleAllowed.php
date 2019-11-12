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

use Symfony\Component\Validator\Constraint;

/**
 * LocaleAllowed Constraint
 *
 * @Annotation
 */
class LocaleAllowed extends Constraint
{
    /**
     * @var string
     */
    public $message = 'The locale "%string%" is not allowed by application configuration.';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'lunetics_locale.validator.locale_allowed';
    }
}