<?php

/**
 * This file is part of the LuneticsLocaleBundle package.
 *
 * <https://github.com/lunetics/LocaleBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */

namespace Lunetics\LocaleBundle\Form\Extension\ChoiceList;

use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Intl\Languages;

/**
 * Locale Choicelist Class
 */
class LocaleChoiceList extends ArrayChoiceList
{
    private $localeChoices;
    private $preferredChoices;

    /**
     * Construct the LocaleChoiceList
     *
     * @param LocaleInformation $information   LocaleInformation Service
     * @param bool              $languagesOnly If only Languages should be displayed
     * @param bool              $strictMode    If strict mode
     */
    public function __construct(LocaleInformation $information, $languagesOnly = true, $strictMode = false)
    {
        $this->localeChoices = array();
        $allowedLocales = $strictMode
            ? $information->getAllowedLocalesFromConfiguration()
            : $information->getAllAllowedLanguages();

        foreach ($allowedLocales as $locale) {
            if ($languagesOnly && strlen($locale) == 2 || !$languagesOnly) {
                try {
                    $this->localeChoices[$locale] = Languages::getName($locale, $locale);
                } catch(MissingResourceException) {
                    $this->localeChoices[$locale] = null;
                }
            }
        }

        $this->preferredChoices = $information->getPreferredLocales();

        parent::__construct($this->localeChoices);
    }

    /**
     * Returns the preferred choices
     *
     * @return array|void
     */
    public function getPreferredChoices()
    {
        return $this->preferredChoices;
    }
}
