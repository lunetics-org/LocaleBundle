<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Tests\Form\Extension\Type;

use Lunetics\LocaleBundle\Form\Extension\Type\LocaleType;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testSetDefaultOptions()
    {
        $choiceList = $this->getMockLocaleChoiceList();

        $resolver = $this->getMockOptionsResolverInterface();
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(array('choice_list' => $choiceList));

        $type = new LocaleType($choiceList);
        $type->setDefaultOptions($resolver);
    }

    public function testGetParent()
    {
        $type = new LocaleType($this->getMockLocaleChoiceList());

        $this->assertEquals('choice', $type->getParent());
    }

    public function testGetName()
    {
        $type = new LocaleType($this->getMockLocaleChoiceList());

        $this->assertEquals('lunetics_locale', $type->getName());
    }

    protected function getMockLocaleChoiceList()
    {
        return $this
            ->getMockBuilder('Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    protected function getMockOptionsResolverInterface()
    {
        return $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }
}