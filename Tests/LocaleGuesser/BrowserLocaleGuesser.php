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
        
    }
    
    public function getIdentifiedLocale()
    {
        
    }
}