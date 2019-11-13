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

use Lunetics\LocaleBundle\Validator\Locale;
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocaleValidatorTest extends BaseMetaValidator
{
    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLanguageIsValid(bool $intlExtension) : void
    {
        $constraint = new Locale();

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('de', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('deu', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('eng', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr', $constraint);

        // Filipino removed from known ISO-639-2 locales in Symfony 2.3+
        // @see https://github.com/symfony/symfony/issues/12583
        //$this->getLocaleValidator($intlExtension)->validate('fil', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithRegionIsValid(bool $intlExtension) : void
    {
        $constraint = new Locale();

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('de_DE', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en_PH', $constraint);  // Filipino English
        $this->getLocaleValidator($intlExtension)->validate('fr_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_CH', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithIso639_2Valid(bool $intlExtension) : void
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
            ->method('addViolation');

        // Filipino removed from known ISO-639-2 locales in Symfony 2.3+
        // @see https://github.com/symfony/symfony/issues/12583
        //$this->getLocaleValidator($intlExtension)->validate('fil_PH', $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithScriptValid(bool $intlExtension) : void
    {
        $constraint = new Locale();
        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('zh_Hant_HK', $constraint);
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsInvalid(bool $intlExtension) : void
    {
        $constraint = new Locale();
        // Need to distinguish, since the intl fallback allows every combination of languages, script and regions
        $this->context
            ->expects($this->exactly(3))
            ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('foobar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('de_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar_baz', $constraint);

    }

    public function testValidateThrowsUnexpectedTypeException() : void
    {
        $this->expectException(UnexpectedTypeException::class);
        $validator = new LocaleValidator();
        $validator->validate([], $this->getMockConstraint());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidateEmptyLocale() : void
    {
        $validator = new LocaleValidator();

        $validator->validate(null, $this->getMockConstraint());
        $validator->validate('', $this->getMockConstraint());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Constraint
     */
    protected function getMockConstraint()
    {
        return $this->createMock(Constraint::class);
    }
}
