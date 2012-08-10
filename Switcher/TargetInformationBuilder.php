<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\Switcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Locale\Locale;

/**
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class TargetInformationBuilder
{   
    private $route;
    
    public function __construct($route = null)
    {
        $this->route = $route;
    }
    
    /**
     * Builds a bunch of informations in order to build a switcher template
     * for custom needs
     * 
     * Will return something like this (let's say current locale is fr :
     * 
     * current_route: hello_route
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
     * @param array $parameters
     * @return array Informations for the switcher template
     */
    public function getTargetInformations(Request $request, RouterInterface $router, $allowedLocales, $paramteters = array())
    {
        $infos = array();
        $route = null !== $this->route ? $this->route : $request->attributes->get('_route');
        $infos['current_locale'] = $request->getLocale();
        $infos['current_route'] = $route;
        $targetLocales = $allowedLocales;
        
        foreach($targetLocales as $locale) {
            // No need to build route and locale names for current locale
            if(0 !== strpos($request->getLocale(), $locale)) {
                $targetLocaleTargetLang = Locale::getDisplayLanguage($locale, $locale);
                $paramteters['_locale'] = $locale;
                $targetRoute = $router->generate($route, $paramteters);
                
                $infos['locales'][$locale] = array(
                    'locale_current_language' => $targetLocaleTargetLang,
                    'link' => $targetRoute,
                    'locale' => $locale,
                    );
            }
        }
        
        return $infos;
    }
}
