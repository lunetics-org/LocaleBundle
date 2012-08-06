<?php
/**
 * This file is part of the LuneticsLocaleBundle package.
 * 
 * <https://github.com/lunetics/LocaleBundle/>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that is distributed with this source code.
 */
namespace Lunetics\LocaleBundle\LocaleGuesser;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Lunetics <http://www.lunetics.com/>
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class BrowserLocaleGuesser implements LocaleGuesserInterface
{
    private $defaultLocale;
    
    private $allowedLocales;
    
    private $identifiedLocale;
    
    private $session;
    
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
    
    /**
     * Guess the locale based on browser settings
     * 
     * @param Request $request
     * @return boolean
     */
    public function guessLocale(Request $request)
    {
        $this->session = $request->getSession();
        
        if($this->sessionLocaleExist()){
            $this->identifiedLocale = $this->session->get('lunetics_locale');
            return true;
        }
        
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
                $this->setSessionLocale($this->identifiedLocale);
                return true;
            }
        } else {
            $this->identifiedLocale = $preferredLanguage;
            $this->setSessionLocale($this->identifiedLocale);
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
    
    public function sessionLocaleExist()
    {
        if($this->session instanceof Session && $this->session->has('lunetics_locale')){
            return true;
        }
        return false;
    }
    
    public function setSessionLocale($locale)
    {
        if($this->session instanceof Session && $this->session->has('lunetics_locale')){
            $this->session->set('lunetics_locale', $locale);
        }
    }
}