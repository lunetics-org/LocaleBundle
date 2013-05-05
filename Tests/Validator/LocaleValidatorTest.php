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

use Lunetics\LocaleBundle\Validator\Locale;
use Lunetics\LocaleBundle\Validator\LocaleValidator;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class LocaleValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected static $iso639;
    protected static $iso3166;
    protected static $script;

    /**
     * Setup
     */
    public function setUp()
    {
        $this->context = $this->getContext();
    }

    public static function setUpBeforeClass()
    {
        $yamlParser = new YamlParser();
        $file = new FileLocator(__DIR__ . '/../../Resources/config');
        self::$iso3166 = $yamlParser->parse(file_get_contents($file->locate('iso3166-1-alpha-2.yml')));
        self::$iso639 = array_merge(
            $yamlParser->parse(file_get_contents($file->locate('iso639-1.yml'))),
            $yamlParser->parse(file_get_contents($file->locate('iso639-2.yml')))
        );
        self::$script = $yamlParser->parse(file_get_contents($file->locate('locale_script.yml')));
    }

    /**
     * Dataprovider for testing each test with and without intl extension
     *
     * @return array
     */
    public function intlExtensionInstalled()
    {
        return array(
            'Extension On' => array(true),
            'Extension Off' => array(false)
        );
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLanguageIsValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('de', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fil', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithRegionIsValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('de_DE', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('en_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_CH', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithIso639_2Valid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('fil_PH', $constraint);
    }

    /**
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleWithScriptValid($intlExtension)
    {
        $constraint = new Locale();
        $this->context->expects($this->never())
                ->method('addViolation');
        $this->getLocaleValidator($intlExtension)->validate('zh_Hant_HK', $constraint);
    }

    /**
     * Test if locale is invalid
     *
     * @param bool $intlExtension
     *
     * @dataProvider intlExtensionInstalled
     */
    public function testLocaleIsInvalid($intlExtension)
    {
        $constraint = new Locale();
        // Need to distinguish, since the intl fallback allows every combination of languages, script and regions
        $this->context->expects($this->exactly(3))
                      ->method('addViolation');

        $this->getLocaleValidator($intlExtension)->validate('foobar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('de_FR', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('fr_US', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar', $constraint);
        $this->getLocaleValidator($intlExtension)->validate('foo_bar_baz', $constraint);

    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testValidateThrowsUnexpectedTypeException()
    {
        $validator = new LocaleValidator();
        $validator->validate(array(), $this->getMockConstraint());
    }

    public function testValidateEmptyLocale()
    {
        $validator = new LocaleValidator();

        $validator->validate(null, $this->getMockConstraint());
        $validator->validate('', $this->getMockConstraint());
    }

    /**
     * Returns an Executioncontext
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContext()
    {
        return $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')->disableOriginalConstructor()->getMock();
    }

    /**
     * Returns the LocaleValidator
     *
     * @param bool $intlExtension
     *
     * @return LocaleValidator
     */
    private function getLocaleValidator($intlExtension = false)
    {
        $validator = new LocaleValidator($intlExtension, self::$iso3166, self::$iso639);
        $validator->initialize($this->context);

        return $validator;
    }

    protected function getMockConstraint()
    {
        return $this->getMock('Symfony\Component\Validator\Constraint');
    }
}
