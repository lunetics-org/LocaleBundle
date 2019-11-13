<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

declare(strict_types=1);

namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\Validator\LocaleAllowed;
use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Test for the LocaleAllowedValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleAllowedValidatorTest extends BaseMetaValidator
{
    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowed(bool $intlExtension) : void
    {
        $constraint = new LocaleAllowed();

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleAllowedValidator($intlExtension, ['en', 'de'], false)->validate('en', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedNonStrict(bool $intlExtension) : void
    {
        $constraint = new LocaleAllowed();

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleAllowedValidator($intlExtension, ['en', 'de'], false)->validate('de_DE', $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testEmptyAllowedList(bool $intlExtension) : void
    {
        $constraint = new LocaleAllowed();

        $this->context
            ->expects($this->once())
            ->method('addViolation');

        $this->getLocaleAllowedValidator($intlExtension, [], false)->validate('en', $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowed(bool $intlExtension) : void
    {
        $locale     = 'fr';
        $constraint = new LocaleAllowed();

        $this->context
            ->expects($this->exactly(2))
            ->method('addViolation')
            ->with($this->equalTo($constraint->message), $this->equalTo(['%string%' => $locale]));

        $this->getLocaleAllowedValidator($intlExtension, ['en', 'de'], false)->validate($locale, $constraint);
        $this->getLocaleAllowedValidator($intlExtension, ['en_US', 'de_DE'], false)->validate($locale, $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedStrict(bool $intlExtension) : void
    {
        $constraint = new LocaleAllowed();
        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleAllowedValidator($intlExtension, ['en', 'de', 'fr'], true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, ['de_AT', 'de_CH', 'fr_FR'], true)->validate('fr_FR', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, ['de_AT', 'en', 'fr'], true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, ['de_AT', 'en', 'fr'], true)->validate('de_AT', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedStrict(bool $intlExtension) : void
    {
        $constraint = new LocaleAllowed();
        $this->context
            ->expects($this->exactly(2))
            ->method('addViolation');

        $this->getLocaleAllowedValidator($intlExtension, ['en', 'de'], true)->validate('de_AT', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, ['en_US', 'de_DE'], true)->validate('de', $constraint);
    }

    public function testValidateThrowsUnexpectedTypeException() : void
    {
        $this->expectException(UnexpectedTypeException::class);
        $validator = new LocaleAllowedValidator();
        $validator->validate([], $this->getMockConstraint());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidateEmptyLocale() : void
    {
        $validator = new LocaleAllowedValidator();

        $validator->validate(null, $this->getMockConstraint());
        $validator->validate('', $this->getMockConstraint());
    }

    protected function getMockConstraint()
    {
        return $this->createMock(Constraint::class);
    }
}
