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
 * @author Christophe Willemsen <willemsen.christophe@gmail.com/>
 */
class CookieLocaleGuesser implements LocaleGuesserInterface
{
    private $identifiedLocale;
    
    private $localeCookieName;
    
    public function __construct($localeCookieName)
    {
        $this->localeCookieName = $localeCookieName;
    }
    
    public function guessLocale(Request $request)
    {
        if($request->cookies->has($this->localeCookieName)){
            $this->identifiedLocale = $request->cookies->get($this->localeCookieName);
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