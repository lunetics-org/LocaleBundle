<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\LocaleGuesser;

use Lunetics\LocaleBundle\LocaleGuesser\BasePathLocaleGuesser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class BasePathLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataDomains
     *
     * @param bool $expected
     * @param string $basePath
     * @param bool $allowed
     */
    public function testGuessLocale($expected, $basePath, $allowed, $seperator)
    {
        $metaValidator = $this->getMockMetaValidator();

        if (null !== $allowed) {
            $metaValidator
                ->expects($this->once())
                ->method('isAllowed')
                ->will($this->returnValue($allowed))
            ;
        }

        $request = $this->getMockRequest();
        $request
            ->expects($this->once())
            ->method('getBasePath')
            ->will($this->returnValue($basePath))
        ;

        $guesser = new BasePathLocaleGuesser($metaValidator, $seperator);

        $this->assertEquals($expected, $guesser->guessLocale($request));
    }

    public function dataDomains()
    {
        return array(
            array(true,  '/en',    true,  null),
            array(true,  '/en/foo',true,  null),
            array(false, '/fr',    false, null),
            array(false, '',       null,  null),
            array(false, '/foo',   false, null),
            array(true,  '/en-ca', true,  '-'),
            array(true,  '/fr_ca', true,  '_'),
            array(false, '/de-DE', false, '_'),
        );
    }
    
    private function getMockMetaValidator()
    {
        return $this
            ->getMockBuilder('\Lunetics\LocaleBundle\Validator\MetaValidator')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    private function getMockRequest()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request');
    }
}
