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

use Lunetics\LocaleBundle\LocaleGuesser\SubdomainLocaleGuesser;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class SubdomainLocaleGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataDomains
     *
     * @param bool $expected
     * @param string $host
     * @param bool $allowed
     */
    public function testGuessLocale($expected, $host, $allowed)
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
            ->method('getHost')
            ->will($this->returnValue($host))
        ;

        $guesser = new SubdomainLocaleGuesser($metaValidator);

        $this->assertEquals($expected, $guesser->guessLocale($request));
    }

    public function dataDomains()
    {
        return array(
            array(true, 'en.domain', true),
            array(false, 'fr.domain', false),
            array(false, 'domain', null),
            array(false, 'www.domain', false),
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
