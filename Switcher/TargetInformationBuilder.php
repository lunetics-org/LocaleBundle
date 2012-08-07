<?php

namespace Lunetics\LocaleBundle\Switcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Locale\Locale;

class TargetInformationBuilder
{   
    
    public function getTargetInformations(Request $request, RouterInterface $router, $allowedLocales)
    {
        $infos = array();
        $route = $request->attributes->get('_route');
        $targetLocales = $allowedLocales;
        
        foreach($targetLocales as $locale) {
            // No need to build route and locale names for current locale
            if($locale !== $request->getLocale()) {
                $targetLocaleCurrentLang = Locale::getDisplayLanguage($locale, $request->getLocale());
                $targetLocaleTargetLang = Locale::getDisplayLanguage($locale, $locale);
                $targetRoute = $router->generate($route, array('_locale' => $locale));
                
                $infos[$locale] = array(
                    'targetLocaleCurrentLang' => $targetLocaleCurrentLang,
                    'targetLocaleTargetLang' => $targetLocaleTargetLang,
                    'targetRoute' => $targetRoute,
                    );
            }
        }
        
        return $infos;
    }
}
