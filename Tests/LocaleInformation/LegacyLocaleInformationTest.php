<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleInformation;

/**
 * Test for the LocaleInformation with deprecated execution context.
 *
 * @author Alexander Schwenn <alexander.schwenn@gmail.com>
 */
class LegacyLocaleInformationTest extends LocaleInformationTest
{
    public function setUp()
    {
        // Only run this test if the new ExecutionContext is available (SF >= 2.5).
        // Then use the deprecated ExecutionContext.
        if (!class_exists('\Symfony\Component\Validator\Context\ExecutionContext')) {
            $this->markTestSkipped();
        }
        parent::setUp();
    }

    public function getContext()
    {
        return $this->getMockBuilder('\Symfony\Component\Validator\ExecutionContext')->disableOriginalConstructor()->getMock();
    }
}
