<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */


namespace Lunetics\LocaleBundle\Tests\Validator;

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Validator\LocaleAllowed;
use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Test for the LocaleAllowedValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleAllowedValidatorTest extends BaseMetaValidator
{
    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowed($intlExtension)
    {
        $constraint = new LocaleAllowed();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleAllowedValidator($intlExtension, array('en', 'de'), false)->validate('en', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedNonStrict($intlExtension)
    {
        $constraint = new LocaleAllowed();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleAllowedValidator($intlExtension, array('en', 'de'), false)->validate('de_DE', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testEmptyAllowedList($intlExtension)
    {
        $constraint = new LocaleAllowed();
        $this->context->expects($this->once())
                ->method('addViolation');
        $this->getLocaleAllowedValidator($intlExtension, array(), false)->validate('en', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowed($intlExtension)
    {
        $locale = 'fr';
        $constraint = new LocaleAllowed();
        $this->context->expects($this->exactly(2))
                ->method('addViolation')
                ->with($this->equalTo($constraint->message), $this->equalTo(array('%string%' => $locale)));
        $this->getLocaleAllowedValidator($intlExtension, array('en', 'de'), false)->validate($locale, $constraint);
        $this->getLocaleAllowedValidator($intlExtension, array('en_US', 'de_DE'), false)->validate($locale, $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedStrict($intlExtension)
    {
        $constraint = new LocaleAllowed();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleAllowedValidator($intlExtension, array('en', 'de', 'fr'), true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, array('de_AT', 'de_CH', 'fr_FR'), true)->validate('fr_FR', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, array('de_AT', 'en', 'fr'), true)->validate('fr', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, array('de_AT', 'en', 'fr'), true)->validate('de_AT', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedStrict($intlExtension)
    {
        $constraint = new LocaleAllowed();
        $this->context->expects($this->exactly(2))
                ->method('addViolation');
        $this->getLocaleAllowedValidator($intlExtension, array('en', 'de'), true)->validate('de_AT', $constraint);
        $this->getLocaleAllowedValidator($intlExtension, array('en_US', 'de_DE'), true)->validate('de', $constraint);
    }

    public function testValidateThrowsUnexpectedTypeException()
    {
        $this->expectException('Symfony\Component\Validator\Exception\UnexpectedTypeException');
        $validator = new LocaleAllowedValidator();
        $validator->validate(array(), $this->getMockConstraint());
    }

    public function testValidateEmptyLocale()
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
