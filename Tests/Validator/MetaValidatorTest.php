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

use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Lunetics\LocaleBundle\Validator\MetaValidator;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
//class MetaValidatorTest extends \PHPUnit_Framework_TestCase
class MetaValidatorTest extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
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
    public function testLocaleIsAllowedNonStrict($intlExtension)
    {
        $metaValidator = $this->getMetaValidator(array('en', 'de'), $intlExtension);
        $this->assertTrue($metaValidator->isAllowed('en'));
        $this->assertTrue($metaValidator->isAllowed('en_US'));
        $this->assertTrue($metaValidator->isAllowed('de'));
        $this->assertTrue($metaValidator->isAllowed('de_AT'));
        if ($intlExtension) {
            $this->assertFalse($metaValidator->isAllowed('de_FR'));
        } else {
            $this->assertTrue($metaValidator->isAllowed('de_FR'));
        }
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
        if ($intlExtension) {
            $this->assertFalse($metaValidator->isAllowed('de_FR'));
        } else {
            $this->assertTrue($metaValidator->isAllowed('de_FR'));
        }

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

    /*
     * Below are the getters for the tests, do not change!
     */

    private function getMetaValidator($allowedLocales = array(), $intlExtension = false, $strict = false)
    {
        $factory = new ConstraintValidatorFactory($this->getLocaleValidator($intlExtension), $this->getLocaleAllowedValidator($intlExtension, $allowedLocales, $strict));
        $validator = Validation::createValidatorBuilder();
        $validator->setConstraintValidatorFactory($factory);

        return new MetaValidator($validator->getValidator());
    }

    private function getContext()
    {
        return $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')->disableOriginalConstructor()->getMock();
    }

    private function getLocaleValidator($intlExtension = false)
    {
        $validator = new LocaleValidator($intlExtension, self::$iso3166, self::$iso639);
        $validator->initialize($this->context);

        return $validator;
    }

    private function getLocaleAllowedValidator($intlExtension = false, $allowedLocales = array(), $strictMode = false)
    {
        $validator = new LocaleAllowedValidator($allowedLocales, $strictMode, $intlExtension);
        $validator->initialize($this->context);

        return $validator;
    }
}

use Symfony\Component\Validator\Constraint;

class ConstraintValidatorFactory implements \Symfony\Component\Validator\ConstraintValidatorFactoryInterface
{
    protected $validators = array();

    protected $localeValidator;

    protected $localeAllowedValidator;

    public function __construct(LocaleValidator $localeValidator, LocaleAllowedValidator $localeAllowedValidator)
    {
        $this->localeValidator = $localeValidator;
        $this->localeAllowedValidator = $localeAllowedValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if ($className == 'lunetics_locale.validator.locale') {
            $this->validators[$className] = $this->localeValidator;
        }

        if ($className == 'lunetics_locale.validator.locale_allowed') {
            $this->validators[$className] = $this->localeAllowedValidator;
        }


        return $this->validators[$className];
    }
}
