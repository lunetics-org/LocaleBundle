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

use Lunetics\LocaleBundle\LocaleInformation\AllowedLocalesProvider;
use Lunetics\LocaleBundle\Validator\LocaleAllowedValidator;
use Lunetics\LocaleBundle\Validator\LocaleValidator;
use Lunetics\LocaleBundle\Validator\MetaValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Test for the LocaleValidator
 *
 * @author Matthias Breddin <mb@lunetics.com>
 */
class BaseMetaValidator extends TestCase
{
    /** @var MockObject|ExecutionContextInterface */
    protected $context;

    /** @var array */
    protected static $iso639;

    /** @var array */
    protected static $iso3166;

    /** @var array */
    protected static $script;

    /**
     * Setup
     */
    public function setUp() : void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
    }

    public static function setUpBeforeClass() : void
    {
        $yamlParser    = new YamlParser();
        $file          = new FileLocator(__DIR__ . '/../../src/Resources/config');
        self::$iso3166 = (array) $yamlParser->parse(file_get_contents($file->locate('iso3166-1-alpha-2.yml')));
        self::$iso639  = array_merge(
            $yamlParser->parse(file_get_contents($file->locate('iso639-1.yml'))),
            $yamlParser->parse(file_get_contents($file->locate('iso639-2.yml')))
        );
        self::$script  = (array) $yamlParser->parse(file_get_contents($file->locate('locale_script.yml')));
    }

    /**
     * Dataprovider for testing each test with and without intl extension
     *
     * @return array
     */
    public function intlExtensionInstalled() : array
    {
        return [
            'Extension On'  => [true],
            'Extension Off' => [false],
        ];
    }

    public function getMetaValidator(array $allowedLocales = [], bool $intlExtension = false, bool $strict = false) : MetaValidator
    {
        $factory   = new \Lunetics\LocaleBundle\Tests\Validator\ContraintValidatorFactory($this->getLocaleValidator($intlExtension), $this->getLocaleAllowedValidator($intlExtension, $allowedLocales, $strict));
        $validator = Validation::createValidatorBuilder();
        $validator->setConstraintValidatorFactory($factory);

        return new MetaValidator($validator->getValidator());
    }

    public function getLocaleValidator($intlExtension = false) : LocaleValidator
    {
        $validator = new LocaleValidator($intlExtension, self::$iso3166, self::$iso639);
        $validator->initialize($this->context);

        return $validator;
    }

    public function getLocaleAllowedValidator($intlExtension = false, $allowedLocales = [], $strictMode = false) : LocaleAllowedValidator
    {
        $allowedLocalesProvider = new AllowedLocalesProvider($allowedLocales);
        $validator              = new LocaleAllowedValidator($allowedLocalesProvider, $strictMode, $intlExtension);
        $validator->initialize($this->context);

        return $validator;
    }
}
