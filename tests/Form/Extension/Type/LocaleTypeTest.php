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

use Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList;
use Lunetics\LocaleBundle\Form\Extension\Type\LocaleType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleTypeTest extends TestCase
{
    public function testConfigureOptions()
    {
        $choiceList = $this->getMockLocaleChoiceList();

        $resolver = $this->getMockOptionsResolverInterface();
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(array('choices' => [], 'preferred_choices' => null));

        $type = new LocaleType($choiceList);
        $type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $type = new LocaleType($this->getMockLocaleChoiceList());

        $this->assertEquals('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', $type->getParent());
    }

    public function testGetName()
    {
        $type = new LocaleType($this->getMockLocaleChoiceList());

        $this->assertEquals('lunetics_locale', $type->getBlockPrefix());
    }

    protected function getMockLocaleChoiceList()
    {
        $mockLocaleChoiceList = $this->createMock(LocaleChoiceList::class);
        $mockLocaleChoiceList->method('getOriginalKeys')->willReturn([]);

        return $mockLocaleChoiceList;
    }

    protected function getMockOptionsResolverInterface()
    {
        return $this->createMock(OptionsResolver::class);
    }
}
