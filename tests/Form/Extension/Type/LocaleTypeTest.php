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

namespace Lunetics\LocaleBundle\Tests\Form\Extension\Type;

use Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList;
use Lunetics\LocaleBundle\Form\Extension\Type\LocaleType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Kevin Archer <ka@kevinarcher.ca>
 */
class LocaleTypeTest extends TestCase
{
    /** @var MockObject|OptionsResolver */
    private $optionsResolver;

    /** @var LocaleChoiceList|MockObject */
    private $localeChoiceList;

    public function setUp() : void
    {
        $this->optionsResolver  = $this->createMock(OptionsResolver::class);
        $this->localeChoiceList = $this->createMock(LocaleChoiceList::class);
    }

    public function testConfigureOptions() : void
    {
        $this->optionsResolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with(['choices' => null, 'preferred_choices' => null]);

        $type = new LocaleType($this->localeChoiceList);
        $type->configureOptions($this->optionsResolver);
    }

    public function testGetParent() : void
    {
        $type = new LocaleType($this->localeChoiceList);

        $this->assertEquals('Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', $type->getParent());
    }

    public function testGetName() : void
    {
        $type = new LocaleType($this->localeChoiceList);

        $this->assertEquals('lunetics_locale', $type->getBlockPrefix());
    }
}
