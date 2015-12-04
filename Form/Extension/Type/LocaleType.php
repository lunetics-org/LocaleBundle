<?php

/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Form\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Lunetics\LocaleBundle\Form\Extension\ChoiceList\LocaleChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom choice type for locales.
 */
class LocaleType extends AbstractType
{
    /**
     * @var LocaleChoiceList
     */
    protected $choiceList;

    /**
     * @param LocaleChoiceList $choiceList
     */
    public function __construct(LocaleChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'choice_list' => $this->choiceList,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lunetics_locale';
    }
}
