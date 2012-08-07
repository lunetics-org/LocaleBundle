<?php

namespace Lunetics\LocaleBundle\Switcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Locale\Locale;

class TargetInformationBuilder
{   
    /**
     * Builds a bunch of informations in order to build a switcher template
     * for custom needs
     * 
     * Will return something like this (let's say current locale is fr :
     * 
     * current_route: http://app_dev.php/...
     * current_locale: fr
     * locales:
     *   en:
     *     link: http://app_dev.php/en/... or http://app_dev.php?_locale=en
     *     locale: en
     *     locale_target_language: English
     *     locale_current_language: Anglais
     * 
     * @param Request $request
     * @param RouterInterface $router
     * @param array $allowedLocales
     * @return array Informations for the switcher template
     */
    public function getTargetInformations(Request $request, RouterInterface $router, $allowedLocales)
    {
        $infos = array();
        $route = $request->attributes->get('_route');
        $infos['current_locale'] = $request->getLocale();
        $infos['current_route'] = $route;
        $targetLocales = $allowedLocales;
        
        foreach($targetLocales as $locale) {
            // No need to build route and locale names for current locale
            if($locale !== $request->getLocale()) {
                $targetLocaleCurrentLang = Locale::getDisplayLanguage($locale, $request->getLocale());
                $targetLocaleTargetLang = Locale::getDisplayLanguage($locale, $locale);
                $targetRoute = $router->generate($route, array('_locale' => $locale));
                
                $infos['locales'][$locale] = array(
                    'locale_current_language' => $targetLocaleCurrentLang,
                    'locale_current_language' => $targetLocaleTargetLang,
                    'link' => $targetRoute,
                    'locale' => $locale,
                    );
            }
        }
        
        return $infos;
    }
}
