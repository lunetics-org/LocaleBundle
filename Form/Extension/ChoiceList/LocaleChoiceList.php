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

use Symfony\Component\Locale\Locale;
use Lunetics\LocaleBundle\LocaleInformation\LocaleInformation;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

/**
 * Locale Choicelist Class
 */
class LocaleChoiceList extends SimpleChoiceList
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

        if ($strictMode) {
            $allowedLocales = $information->getAllowedLocalesFromConfiguration();
        } else {
            $allowedLocales = $information->getAllAllowedLanguages();
        }

        foreach ($allowedLocales as $locale) {
            if ($languagesOnly && strlen($locale) == 2 || !$languagesOnly) {
                $this->localeChoices[$locale] = Locale::getDisplayName($locale, $locale);
            }
        }

        $this->preferredChoices = $information->getPreferredLocales();

        parent::__construct($this->localeChoices, $this->preferredChoices);
    }

    /**
     * Returns the preferred views, sorted by the ->preferredChoices list
     *
     * @return array|void
     */
    public function getPreferredViews()
    {
        $preferredViews = parent::getPreferredViews();
        $result = array();
        foreach ($this->preferredChoices as $pchoice) {
            foreach ($preferredViews as $view) {
                if ($pchoice == $view->data) {
                    $result[] = $view;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the remaining locales sorted by language name
     * TODO: Use Collator->sort for utf-8 locale-strings?
     *
     * @return array
     */
    public function getRemainingViews()
    {
        $remainingViews = parent::getRemainingViews();
        sort($remainingViews);

        return $remainingViews;
    }

}