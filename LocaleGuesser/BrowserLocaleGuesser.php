<?php

namespace Lunetics\LocaleBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;

class BrowserLocaleGuesser implements LocaleGuesserInterface
{
    private $defaultLocale;
    
    private $allowedLocales;
    
    private $identifiedLocale;
    
    /**
     * Constructor
     * 
     * @param type $defaultLocale
     * @param array $allowedLocales
     */
    public function __construct($defaultLocale, array $allowedLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->allowedLocales = $allowedLocales;
    }
    
    public function guessLocale(Request $request)
    {
        // Get the Preferred Language from the Browser
        $preferredLanguage = $request->getPreferredLanguage();
        $providedLanguages = $request->getLanguages();
        
        if (!$preferredLanguage OR count($providedLanguages) === 0){
            return false;
        }

        if (!in_array(\Locale::getPrimaryLanguage($preferredLanguage), $this->allowedLocales)) {
            $availableLanguages = $this->allowedLocales;
            $map = function($v) use ($availableLanguages)
            {
                if (in_array(\Locale::getPrimaryLanguage($v), $availableLanguages)) {
                    return true;
                }
            };
            $result = array_values(array_filter($providedLanguages, $map));
            if (!empty($result)) {
                $this->identifiedLocale = $result[0];
                return true;
            }
        } else {
            $this->identifiedLocale = $preferredLanguage;
            return true;
        }
        return false;
    }
    
    public function getIdentifiedLocale()
    {
        if(null === $this->identifiedLocale){
            return false;
        }
        return $this->identifiedLocale;
    }
}