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

class MetaValidatorTest extends BaseMetaValidator
{
    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedNonStrict(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de'], $intlExtension, false);
        $this->assertTrue($metaValidator->isAllowed('en'));
        $this->assertTrue($metaValidator->isAllowed('en_US'));
        $this->assertTrue($metaValidator->isAllowed('de'));
        $this->assertTrue($metaValidator->isAllowed('de_AT'));
        $this->assertTrue($metaValidator->isAllowed('de_FR'));
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedNonStrict(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de'], $intlExtension, false);
        $this->assertFalse($metaValidator->isAllowed('fr'));
        $this->assertFalse($metaValidator->isAllowed('fr_FR'));
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsAllowedStrict(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de_AT'], $intlExtension, true);
        $this->assertTrue($metaValidator->isAllowed('en'));
        $this->assertTrue($metaValidator->isAllowed('de_AT'));
    }

    /**
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsNotAllowedStrict(bool $intlExtension) : void
    {
        $metaValidator = $this->getMetaValidator(['en', 'de_AT'], $intlExtension, true);
        $this->assertfalse($metaValidator->isAllowed('en_US'));
        $this->assertfalse($metaValidator->isAllowed('de'));
    }
}
