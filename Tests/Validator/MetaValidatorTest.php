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

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class MetaValidatorTest extends BaseMetaValidator
{
    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedNonStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator(array('en', 'de'), $intlExtension);
        $this->assertTrue($metaValidator->isAllowed('en'));
        $this->assertTrue($metaValidator->isAllowed('en_US'));
        $this->assertTrue($metaValidator->isAllowed('de'));
        $this->assertTrue($metaValidator->isAllowed('de_AT'));
        $this->assertTrue($metaValidator->isAllowed('de_FR'));
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedNonStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator(array('en', 'de'), $intlExtension);
        $this->assertFalse($metaValidator->isAllowed('fr'));
        $this->assertFalse($metaValidator->isAllowed('fr_FR'));
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator(array('en', 'de_AT'), $intlExtension, true);
        $this->assertTrue($metaValidator->isAllowed('en'));
        $this->assertTrue($metaValidator->isAllowed('de_AT'));
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator(array('en', 'de_AT'), $intlExtension, true);
        $this->assertfalse($metaValidator->isAllowed('en_US'));
        $this->assertfalse($metaValidator->isAllowed('de'));
    }
}
