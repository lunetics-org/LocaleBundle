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
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Lunetics\LocaleBundle\Validator\MetaValidator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class BaseMetaValidator extends TestCase
{
    protected $context;
    protected static $iso639;
    protected static $iso3166;
    protected static $script;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->context = $this->getContext();
    }

    public static function setUpBeforeClass(): void
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

    public function getMetaValidator($allowedLocales = array(), $intlExtension = false, $strict = false)
    {
        $factory = new ConstraintValidatorFactory($this->getLocaleValidator($intlExtension), $this->getLocaleAllowedValidator($intlExtension, $allowedLocales, $strict));
        $validator = Validation::createValidatorBuilder();
        $validator->setConstraintValidatorFactory($factory);

        return new MetaValidator($validator->getValidator());
    }

    public function getContext()
    {
        // Run tests against non-deprecated ExecutionContext, if possible
        if (class_exists('\Symfony\Component\Validator\Context\ExecutionContext')) {
            return $this->getMockBuilder('\Symfony\Component\Validator\Context\ExecutionContext')->disableOriginalConstructor()->getMock();
        } else {
            // use deprecated ExecutionContext otherwise
            return $this->getMockBuilder('\Symfony\Component\Validator\ExecutionContext')->disableOriginalConstructor()->getMock();
        }
    }

    public function getLocaleValidator($intlExtension = false)
    {
        $validator = new LocaleValidator($intlExtension, self::$iso3166, self::$iso639);
        $validator->initialize($this->context);

        return $validator;
    }

    public function getLocaleAllowedValidator($intlExtension = false, $allowedLocales = array(), $strictMode = false)
    {
        $allowedLocalesProvider = new AllowedLocalesProvider($allowedLocales);
        $validator = new LocaleAllowedValidator($allowedLocalesProvider, $strictMode, $intlExtension);
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
