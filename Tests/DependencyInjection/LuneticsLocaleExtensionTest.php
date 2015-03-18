<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lunetics\LocaleBundle\DependencyInjection\LuneticsLocaleExtension;
use Symfony\Component\Yaml\Parser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LuneticsLocaleExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getFullConfig
     */
    public function testLoad($configs, $strictMatch)
    {
        $loader = new LuneticsLocaleExtension();
        $container = new ContainerBuilder();

        $loader->load($configs, $container);

        $this->assertTrue($container->hasParameter('lunetics_locale.allowed_locales'));
        $this->assertTrue($container->hasParameter('lunetics_locale.intl_extension_installed'));
        $this->assertTrue($container->hasParameter('lunetics_locale.topleveldomain.locale_map'));

        $this->assertArrayHasKey('be', $container->getParameter('lunetics_locale.topleveldomain.locale_map'));

        $this->assertTrue($container->hasParameter('lunetics_locale.domain.locale_map'));
        $this->assertArrayHasKey('sub.dutchversion.be', $container->getParameter('lunetics_locale.domain.locale_map'));
        $this->assertArrayHasKey('dutchversion.be', $container->getParameter('lunetics_locale.domain.locale_map'));
        $this->assertArrayHasKey('dutch-version.be', $container->getParameter('lunetics_locale.domain.locale_map'));

        if (extension_loaded('intl')) {
            $this->assertEquals(array(), $container->getParameter('lunetics_locale.intl_extension_fallback.iso3166'));
            $this->assertEquals(array(), $container->getParameter('lunetics_locale.intl_extension_fallback.iso639'));
            $this->assertEquals(array(), $container->getParameter('lunetics_locale.intl_extension_fallback.script'));
        } else {
            $this->assertGreaterThan(0, count($container->getParameter('lunetics_locale.intl_extension_fallback.iso3166')));
            $this->assertGreaterThan(0, count($container->getParameter('lunetics_locale.intl_extension_fallback.iso639')));
            $this->assertGreaterThan(0, count($container->getParameter('lunetics_locale.intl_extension_fallback.script')));
        }

        $this->assertEquals($strictMatch, $container->hasDefinition('lunetics_locale.best_locale_matcher'));

        $resources = $container->getResources();

        $this->assertContains('validator.xml', $resources[0]->getResource());
        $this->assertContains('guessers.xml', $resources[1]->getResource());
        $this->assertContains('services.xml', $resources[2]->getResource());
        $this->assertContains('switcher.xml', $resources[3]->getResource());
        $this->assertContains('form.xml', $resources[4]->getResource());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "guessing_order" at path "lunetics_locale" must be configured.
     */
    public function testBundleLoadThrowsExceptionUnlessGuessingOrderIsSet()
    {
        $loader = new LuneticsLocaleExtension();
        $loader->load(array(), new ContainerBuilder());
    }

    public function testGetAlias()
    {
        $loader = new LuneticsLocaleExtension();
        $this->assertEquals('lunetics_locale', $loader->getAlias());
    }

    public function testBindParameters()
    {
        $loader = new LuneticsLocaleExtension();
        $container = new ContainerBuilder();

        $config = array(
            'key' => 'value',
        );

        $loader->bindParameters($container, $loader->getAlias(), $config);

        $this->assertTrue($container->hasParameter('lunetics_locale.key'));
        $this->assertEquals('value', $container->getParameter('lunetics_locale.key'));
    }

    public function getFullConfig()
    {

        $parser = new Parser();
        $data = array();

        $yaml = <<<EOF
lunetics_locale:
  allowed_locales:
    - en
    - fr
    - de
  guessing_order:
    - session
    - cookie
    - browser
    - query
    - router
  topleveldomain:
    locale_map:
      com: en_US
      be: nl_BE
  domain:
    locale_map:
      sub.dutchversion.be: en_BE
      frechversion.be: fr_BE
      dutchversion.be: nl_BE
      dutch-version.be: nl_BE
EOF;
        $data[]=array($parser->parse($yaml), false);

        $yaml = <<<EOF
lunetics_locale:
  strict_match: true
  allowed_locales:
    - en
    - fr
    - de
  guessing_order:
    - session
    - cookie
    - browser
    - query
    - router
  topleveldomain:
    locale_map:
      com: en_US
      be: nl_BE
  domain:
    locale_map:
      sub.dutchversion.be: en_BE
      frechversion.be: fr_BE
      dutchversion.be: nl_BE
      dutch-version.be: nl_BE
EOF;
        $data[]=array($parser->parse($yaml), true);

        return  $data;
    }
}
